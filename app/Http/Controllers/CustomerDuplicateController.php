<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerServiceVisit;
use App\Models\DepositInvoice;
use App\Models\LoyaltyTransaction;
use App\Models\SmsLog;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Finding and merging duplicate customer records.
 *
 * The merge is the highest-risk write in the customer module, because the
 * platform points at a customer by two different ids: appointments and SMS logs
 * key on `users.id`, while wallet, loyalty, notes and invoices key on
 * `customers.id`. A merge has to re-point *every* one of them, and missing one
 * silently strands a customer's history on a record that no longer exists.
 *
 * The list below is therefore exhaustive and deliberately explicit rather than
 * clever: each table is named, with the id it uses spelled out.
 */
class CustomerDuplicateController extends Controller
{
    /**
     * Candidate duplicates for this business.
     */
    public function index()
    {
        if (!Auth::user()->isAbleTo('customer manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customers = Customer::with('customer')
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->get();

        return view('customer.duplicates', ['groups' => $this->findDuplicates($customers)]);
    }

    /**
     * Group customers that look like the same person.
     *
     * Three signals, strongest first: an identical mobile number, an identical
     * email, then an identical normalised name. Matching on name alone is the
     * weakest of the three and is offered as a suggestion for a human to confirm,
     * never as grounds to merge automatically.
     *
     * @return array<int,array{reason:string,value:string,customers:array}>
     */
    protected function findDuplicates($customers): array
    {
        $byMobile = [];
        $byEmail = [];
        $byName = [];

        foreach ($customers as $customer) {
            $user = $customer->customer;

            if (!empty($user->mobile_no)) {
                // Compare digits only: "0412 345 678" and "+61412345678" are the
                // same phone written two ways.
                $key = substr(preg_replace('/\D/', '', $user->mobile_no), -9);

                if (strlen($key) >= 8) {
                    $byMobile[$key][] = $customer;
                }
            }

            if (!empty($user->email)) {
                $byEmail[mb_strtolower(trim($user->email))][] = $customer;
            }

            $name = preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $customer->name)));

            if ($name !== '') {
                $byName[$name][] = $customer;
            }
        }

        $groups = [];
        $seen = [];

        foreach (
            [
                ['mobile', __('Same mobile number'), $byMobile],
                ['email', __('Same email address'), $byEmail],
                ['name', __('Same name'), $byName],
            ] as [$type, $label, $index]
        ) {
            foreach ($index as $value => $matches) {
                if (count($matches) < 2) {
                    continue;
                }

                // A pair already surfaced by a stronger signal is not repeated.
                $signature = implode('-', collect($matches)->pluck('id')->sort()->all());

                if (isset($seen[$signature])) {
                    continue;
                }

                $seen[$signature] = true;

                $groups[] = [
                    'reason' => $label,
                    'type' => $type,
                    'value' => (string) $value,
                    'customers' => $matches,
                ];
            }
        }

        return $groups;
    }

    /**
     * The merge confirmation screen.
     */
    public function mergeForm(Request $request)
    {
        if (!Auth::user()->isAbleTo('customer edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ids = array_filter((array) $request->input('ids', []));

        $customers = Customer::with('customer')
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->whereIn('id', $ids)
            ->get();

        if ($customers->count() < 2) {
            return redirect()->route('customer.duplicates')
                ->with('error', __('Select at least two records to merge.'));
        }

        // Counts per record, so whoever confirms the merge can see what will move.
        $counts = [];

        foreach ($customers as $customer) {
            $counts[$customer->id] = $this->ownedRecordCounts($customer);
        }

        return view('customer.merge', compact('customers', 'counts'));
    }

    /**
     * Merge duplicates into one surviving record.
     */
    public function merge(Request $request)
    {
        if (!Auth::user()->isAbleTo('customer edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'primary_id' => 'required|numeric',
            'ids' => 'required|array|min:2',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $customers = Customer::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->whereIn('id', $request->input('ids'))
            ->get();

        $primary = $customers->firstWhere('id', (int) $request->input('primary_id'));

        if (empty($primary) || $customers->count() < 2) {
            return redirect()->route('customer.duplicates')->with('error', __('Nothing to merge.'));
        }

        $duplicates = $customers->reject(fn($customer) => $customer->id === $primary->id);

        try {
            DB::transaction(function () use ($primary, $duplicates, $businessId) {
                foreach ($duplicates as $duplicate) {
                    $this->repoint($duplicate, $primary, $businessId);
                }

                // The wallet balance is a cache, so it has to be rebuilt from the
                // ledger once every duplicate's rows point at the survivor.
                $primary->reconcileWallet();

                foreach ($duplicates as $duplicate) {
                    // The users row goes too — leaving it orphaned would put the
                    // duplicate straight back into the candidate list.
                    User::where('id', $duplicate->user_id)->delete();
                    $duplicate->delete();
                }
            });
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('customer.duplicates')
                ->with('error', __('The merge failed and nothing was changed.'));
        }

        return redirect()->route('customer.profile', $primary->id)
            ->with('success', __(':count records merged into this one.', ['count' => $duplicates->count() + 1]));
    }

    /**
     * Move everything owned by $duplicate onto $primary.
     *
     * Every table the platform links a customer through is listed here. The
     * comment on each says which id it keys on, because that is exactly the
     * distinction that makes this operation dangerous.
     */
    protected function repoint(Customer $duplicate, Customer $primary, $businessId): void
    {
        // --- Keyed on users.id -------------------------------------------
        Appointment::where('business_id', $businessId)
            ->where('customer_id', $duplicate->user_id)
            ->update(['customer_id' => $primary->user_id]);

        DepositInvoice::where('business_id', $businessId)
            ->where('customer_id', $duplicate->user_id)
            ->update(['customer_id' => $primary->user_id]);

        SmsLog::where('business_id', $businessId)
            ->where('user_id', $duplicate->user_id)
            ->update(['user_id' => $primary->user_id]);

        // --- Keyed on customers.id ---------------------------------------
        WalletTransaction::where('customer_id', $duplicate->id)
            ->update(['customer_id' => $primary->id]);

        LoyaltyTransaction::where('customer_id', $duplicate->id)
            ->update(['customer_id' => $primary->id]);

        CustomerNote::where('customer_id', $duplicate->id)
            ->update(['customer_id' => $primary->id]);

        // Visit counters are unique per (customer, service, business), so they
        // are summed rather than blindly re-pointed — a straight update would
        // collide on the unique key and lose the row.
        foreach (CustomerServiceVisit::where('customer_id', $duplicate->id)->get() as $visit) {
            $existing = CustomerServiceVisit::where('customer_id', $primary->id)
                ->where('service_id', $visit->service_id)
                ->where('business_id', $visit->business_id)
                ->first();

            if (empty($existing)) {
                $visit->customer_id = $primary->id;
                $visit->save();
                continue;
            }

            $existing->visits += $visit->visits;
            $existing->free_visit_available = $existing->free_visit_available || $visit->free_visit_available;
            $existing->save();
            $visit->delete();
        }

        // --- Profile fields the survivor should inherit -------------------
        // Points are additive; the risk flag and any filled-in profile field are
        // kept if either record had them, since losing a high-risk flag in a
        // merge would quietly switch off that customer's deposit requirement.
        $primary->loyalty_points += (int) $duplicate->loyalty_points;
        $primary->is_high_risk = $primary->is_high_risk || $duplicate->is_high_risk;
        $primary->gender = $primary->gender ?: $duplicate->gender;
        $primary->dob = $primary->dob ?: $duplicate->dob;

        if (!empty($duplicate->description)) {
            $primary->description = trim($primary->description . "\n" . $duplicate->description);
        }

        $primary->save();

        // Contact details fill gaps only — never overwrite the survivor's own.
        $primaryUser = $primary->customer;
        $duplicateUser = $duplicate->customer;

        if (!empty($primaryUser) && !empty($duplicateUser)) {
            $primaryUser->mobile_no = $primaryUser->mobile_no ?: $duplicateUser->mobile_no;
            $primaryUser->email = $primaryUser->email ?: $duplicateUser->email;
            $primaryUser->save();
        }
    }

    /**
     * How much history each candidate record owns.
     */
    protected function ownedRecordCounts(Customer $customer): array
    {
        return [
            'appointments' => Appointment::where('business_id', $customer->business_id)
                ->where('customer_id', $customer->user_id)->count(),
            'invoices' => DepositInvoice::where('business_id', $customer->business_id)
                ->where('customer_id', $customer->user_id)->count(),
            'wallet' => WalletTransaction::where('customer_id', $customer->id)->count(),
            'notes' => CustomerNote::where('customer_id', $customer->id)->count(),
            'messages' => SmsLog::where('business_id', $customer->business_id)
                ->where('user_id', $customer->user_id)->count(),
        ];
    }
}
