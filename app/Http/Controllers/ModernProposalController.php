<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Workdo\Invoice\Entities\Proposal;
use Workdo\ProductService\Entities\ProductService;
use Workdo\ProductService\Entities\Tax;
use Workdo\Quotation\Entities\ProposalProduct;

/**
 * A from-scratch "Create Quotation" form for the modern-quotation pilot —
 * reached only when Super Admin Settings > "Use Modern Quotation" is on,
 * via ProposalController::create()'s branch to here.
 *
 * It writes the exact same Proposal/ProposalProduct rows and field
 * conventions the original "product" (Accounting) quotation form already
 * uses — no Workdo module code is touched by this class; turning the pilot
 * off returns everyone to the original form untouched. Unlike
 * ModernInvoiceController, there's no payment tab here — a quotation has
 * nothing to record a payment against.
 */
class ModernProposalController extends Controller
{
    public function create()
    {
        if (!Auth::user()->isAbleTo('proposal create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('proposal.modern-create', $this->sharedFormData());
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('proposal create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateRequest($request);

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $customerRow = Customer::where('user_id', $validated['customer_id'])
            ->where('business_id', $businessId)
            ->first();

        if (!$customerRow) {
            return redirect()->back()->with('error', __('Customer not found.'))->withInput();
        }

        // Same source the original create()/edit() forms use for the next
        // quotation number preview — the module's own actual save path
        // (proposalNumber(), reading company_setting('proposal_starting_number'))
        // never advances that counter, so it isn't a reliable source for a
        // real sequential number.
        $nextId = DB::select("SHOW TABLE STATUS LIKE 'proposals'")[0]->Auto_increment;

        $proposal = new Proposal();
        $proposal->proposal_id = $nextId;
        $proposal->customer_id = $validated['customer_id'];
        $proposal->issue_date = $validated['issue_date'];
        $proposal->business_id = $businessId;
        $proposal->created_by = $createdBy;
        $proposal->status = 0; // Draft
        $proposal->account_type = 'Accounting';
        $proposal->proposal_module = 'account';
        $proposal->notes = $validated['notes'] ?? null;
        $proposal->custom_field = json_encode($this->sanitizeCustomFields($request->input('custom_field', [])));
        $proposal->save();

        $this->saveItems($proposal, $validated['items'], $businessId);

        return redirect()->route('proposal.show', Crypt::encrypt($proposal->id))
            ->with('success', __('The proposal has been created successfully'));
    }

    public function edit($e_id)
    {
        if (!Auth::user()->isAbleTo('proposal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($e_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Proposal Not Found.'));
        }

        $businessId = getActiveBusiness();
        $proposal = Proposal::where('id', $id)->where('business_id', $businessId)->first();

        if (!$proposal) {
            return redirect()->back()->with('error', __('Proposal Not Found.'));
        }

        // Each existing line has to say whether it's a Service or a Parts row
        // so the form can pick the right dropdown and pre-select the right
        // catalogue entry — same resolution ModernInvoiceController::edit()
        // uses, since a quotation's items also always land in ProductService.
        $items = $proposal->items->map(function ($item) use ($businessId) {
            $productService = ProductService::where('id', $item->product_id)
                ->where('business_id', $businessId)
                ->first();

            $isService = $productService && $productService->type === 'service' && !empty($productService->service_id);

            return [
                'item_type' => $isService ? 'service' : 'parts',
                'item_id' => $isService ? $productService->service_id : $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount,
            ];
        })->values();

        $customerUser = $proposal->customer;
        $selectedCustomer = $customerUser ? [
            'id' => $proposal->customer_id,
            'name' => $customerUser->name,
            'mobile' => $customerUser->mobile_no,
            'email' => $customerUser->email,
        ] : null;

        return view('proposal.modern-create', array_merge(
            $this->sharedFormData(),
            [
                'selectedCustomer' => $selectedCustomer,
                'proposal' => $proposal,
                'items' => $items,
                'e_id' => $e_id,
                'notes' => $proposal->notes,
                'existingCustomFieldValues' => json_decode($proposal->custom_field ?? '', true) ?: [],
            ]
        ));
    }

    public function update(Request $request, $e_id)
    {
        if (!Auth::user()->isAbleTo('proposal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($e_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Proposal Not Found.'));
        }

        $businessId = getActiveBusiness();
        $proposal = Proposal::where('id', $id)->where('business_id', $businessId)->first();

        if (!$proposal) {
            return redirect()->back()->with('error', __('Proposal Not Found.'));
        }

        $validated = $this->validateRequest($request);

        $customerRow = Customer::where('user_id', $validated['customer_id'])
            ->where('business_id', $businessId)
            ->first();

        if (!$customerRow) {
            return redirect()->back()->with('error', __('Customer not found.'))->withInput();
        }

        $proposal->customer_id = $validated['customer_id'];
        $proposal->issue_date = $validated['issue_date'];
        $proposal->notes = $validated['notes'] ?? null;
        $proposal->custom_field = json_encode($this->sanitizeCustomFields($request->input('custom_field', [])));
        $proposal->save();

        // Full replace rather than diffing — the form always submits the
        // complete current set of rows (added/removed client-side), so a
        // delete-then-recreate can't leave a stale row behind.
        $proposal->items()->delete();
        $this->saveItems($proposal, $validated['items'], $businessId);

        return redirect()->route('proposal.show', Crypt::encrypt($proposal->id))
            ->with('success', __('The proposal has been updated successfully'));
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|numeric',
            'issue_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,parts',
            'items.*.item_id' => 'required|numeric',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    /**
     * Service grouped by category, and Parts as a flat list — identical
     * catalogue resolution to ModernInvoiceController::sharedFormData(),
     * since a quotation's line items come from the exact same source.
     */
    protected function sharedFormData(): array
    {
        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $services = Service::with('Category')
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->get();

        $catalogByServiceId = ProductService::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('type', 'service')
            ->whereNotNull('service_id')
            ->get(['service_id', 'tax_id'])
            ->keyBy('service_id');

        $serviceGroups = [];

        foreach ($services as $service) {
            $category = $service->Category->name ?? __('Uncategorised');

            if (!isset($serviceGroups[$category])) {
                $serviceGroups[$category] = ['category' => $category, 'items' => []];
            }

            $serviceGroups[$category]['items'][] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'tax_id' => optional($catalogByServiceId->get($service->id))->tax_id,
            ];
        }

        $parts = ProductService::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('type', 'product')
            ->orderBy('name')
            ->get(['id', 'name', 'sale_price', 'tax_id']);

        $taxRates = Tax::where('business_id', $businessId)
            ->get(['id', 'rate'])
            ->pluck('rate', 'id');

        return [
            'serviceGroups' => array_values($serviceGroups),
            'parts' => $parts,
            'taxRates' => $taxRates,
            // Proposal::getTotal() always adds tax on top (no inclusive
            // branch the way Invoice::getTotal() has), so the live preview
            // never needs to render a "tax already included" state.
            'currencySymbol' => company_setting('defult_currancy_symbol') ?: '$',
            'currencySymbolPost' => company_setting('site_currency_symbol_position') == 'post',
            'customFields' => $this->customFieldDefinitions(),
        ];
    }

    /**
     * The business's custom-field definitions flagged for the quotation
     * form — identical to ModernInvoiceController::customFieldDefinitions(),
     * just filtered by `show_in_quotation` instead of `show_in_invoice`.
     * Values land in proposals.custom_field as a label => value JSON map.
     *
     * @return array<int,array{label:string,type:string,value:string|null}>
     */
    protected function customFieldDefinitions(): array
    {
        if (company_setting('custom_field_enable', Auth::id(), getActiveBusiness()) !== 'on') {
            return [];
        }

        return CustomField::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->where('show_in_quotation', 1)
            ->orderBy('id')
            ->get(['label', 'type', 'value'])
            ->map(fn ($field) => ['label' => $field->label, 'type' => $field->type, 'value' => $field->value])
            ->values()
            ->all();
    }

    /**
     * Keep only values whose label matches a defined field, so the stored
     * JSON can't be stuffed with arbitrary keys from the client.
     *
     * @return array<string,string>
     */
    protected function sanitizeCustomFields($submitted): array
    {
        if (!is_array($submitted)) {
            return [];
        }

        $allowed = array_column($this->customFieldDefinitions(), 'label');
        $clean = [];

        foreach ($submitted as $label => $value) {
            if (!in_array($label, $allowed, true)) {
                continue;
            }

            $clean[$label] = is_array($value) ? implode(',', $value) : (string) $value;
        }

        return $clean;
    }

    /**
     * @param  array<int,array{item_type:string,item_id:int,description?:string,quantity:float,price:float,discount?:float}>  $items
     */
    protected function saveItems(Proposal $proposal, array $items, $businessId): void
    {
        foreach ($items as $line) {
            if ($line['item_type'] === 'service') {
                $service = Service::where('id', $line['item_id'])
                    ->where('business_id', $businessId)
                    ->first();

                if (!$service) {
                    continue;
                }

                $catalogEntry = ProductService::where('service_id', $service->id)
                    ->where('business_id', $businessId)
                    ->first();

                $fallbackName = $service->name;
            } else {
                $catalogEntry = ProductService::where('id', $line['item_id'])
                    ->where('business_id', $businessId)
                    ->where('type', 'product')
                    ->first();

                $fallbackName = optional($catalogEntry)->name;
            }

            if (!$catalogEntry) {
                continue;
            }

            $proposalProduct = new ProposalProduct();
            $proposalProduct->proposal_id = $proposal->id;
            $proposalProduct->product_id = $catalogEntry->id;
            $proposalProduct->product_type = $line['item_type'] === 'service' ? 'service' : 'product';
            $proposalProduct->tax = $catalogEntry->tax_id;
            $proposalProduct->discount = $line['discount'] ?? 0;
            $proposalProduct->quantity = $line['quantity'];
            $proposalProduct->price = $line['price'];
            $proposalProduct->description = !empty($line['description']) ? $line['description'] : $fallbackName;
            $proposalProduct->save();
        }
    }
}
