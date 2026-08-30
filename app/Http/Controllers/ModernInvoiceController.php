<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Workdo\Invoice\Entities\Invoice;
use Workdo\Invoice\Entities\InvoiceProduct;
use Workdo\ProductService\Entities\ProductService;
use Workdo\ProductService\Entities\Tax;

/**
 * A from-scratch "Create Invoice" form for the modern-invoice pilot — reached
 * only when Super Admin Settings > "Use Modern Invoice" is on, via
 * InvoiceController::create()'s branch to here.
 *
 * It writes the exact same Invoice/InvoiceProduct rows and field conventions
 * the original form (and InvoiceController::convertToInvoice(), the
 * appointment-checkout path) already use, tagged `invoice_module = 'manual'`
 * so these are identifiable but otherwise ordinary invoices — only the form
 * in front of them is different. No Workdo module code is touched by this
 * class; turning the pilot off returns everyone to the original form
 * untouched.
 */
class ModernInvoiceController extends Controller
{
    public function create()
    {
        if (!Auth::user()->isAbleTo('invoice create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('invoice.modern-create', $this->sharedFormData());
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('invoice create')) {
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

        // Same source InvoiceController::create()/convertToInvoice() both use
        // for the next invoice number — not Invoice::invoiceNumber() (private
        // to that controller), but identical logic.
        $nextId = DB::select("SHOW TABLE STATUS LIKE 'invoices'")[0]->Auto_increment;

        $invoice = new Invoice();
        $invoice->customer_id = $customerRow->id;
        $invoice->user_id = $validated['customer_id'];
        $invoice->issue_date = $validated['issue_date'];
        $invoice->due_date = $validated['due_date'];
        $invoice->business_id = $businessId;
        $invoice->created_by = $createdBy;
        $invoice->category_id = 2;
        $invoice->shipping_display = 0;
        $invoice->account_type = 'Account';
        $invoice->module = 'account';
        $invoice->invoice_id = $nextId;
        $invoice->status = 0; // Draft
        $invoice->invoice_module = 'manual';
        $invoice->notes = $validated['notes'] ?? null;
        $invoice->custom_field = json_encode($this->sanitizeCustomFields($request->input('custom_field', [])));
        $invoice->save();

        $this->saveItems($invoice, $validated['items'], $businessId);

        return redirect()->route('invoice.show', Crypt::encrypt($invoice->id))
            ->with('success', __('Invoice successfully created.'));
    }

    public function edit($e_id)
    {
        if (!Auth::user()->isAbleTo('invoice edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($e_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }

        $businessId = getActiveBusiness();
        $invoice = Invoice::where('id', $id)->where('business_id', $businessId)->first();

        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }

        // Each existing line has to say whether it's a Service or a Parts row
        // so the form can pick the right dropdown and pre-select the right
        // catalogue entry — a Service row's item_id is the Service (not
        // ProductService) id, resolved back via the same service_id link
        // saveItems() uses going the other way.
        $items = $invoice->items->map(function ($item) use ($businessId) {
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

        $customerUser = $invoice->customer;
        $selectedCustomer = $customerUser ? [
            'id' => $invoice->user_id,
            'name' => $customerUser->name,
            'mobile' => $customerUser->mobile_no,
            'email' => $customerUser->email,
        ] : null;

        return view('invoice.modern-create', array_merge(
            $this->sharedFormData(),
            [
                'selectedCustomer' => $selectedCustomer,
                'invoice' => $invoice,
                'items' => $items,
                'e_id' => $e_id,
                'notes' => $invoice->notes,
                'existingCustomFieldValues' => json_decode($invoice->custom_field ?? '', true) ?: [],
            ]
        ));
    }

    public function update(Request $request, $e_id)
    {
        if (!Auth::user()->isAbleTo('invoice edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($e_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }

        $businessId = getActiveBusiness();
        $invoice = Invoice::where('id', $id)->where('business_id', $businessId)->first();

        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }

        $validated = $this->validateRequest($request);

        $customerRow = Customer::where('user_id', $validated['customer_id'])
            ->where('business_id', $businessId)
            ->first();

        if (!$customerRow) {
            return redirect()->back()->with('error', __('Customer not found.'))->withInput();
        }

        $invoice->customer_id = $customerRow->id;
        $invoice->user_id = $validated['customer_id'];
        $invoice->issue_date = $validated['issue_date'];
        $invoice->due_date = $validated['due_date'];
        $invoice->notes = $validated['notes'] ?? null;
        $invoice->custom_field = json_encode($this->sanitizeCustomFields($request->input('custom_field', [])));
        $invoice->save();

        // Full replace rather than diffing — the form always submits the
        // complete current set of rows (added/removed client-side), so a
        // delete-then-recreate can't leave a stale row behind.
        $invoice->items()->delete();
        $this->saveItems($invoice, $validated['items'], $businessId);

        return redirect()->route('invoice.show', Crypt::encrypt($invoice->id))
            ->with('success', __('Invoice successfully updated.'));
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|numeric',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
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
     * Service grouped by category, and Parts as a flat list — the two
     * catalogues the "Item Type" switch on the form picks between.
     *
     * Services deliberately come from the booking app's own Service catalog
     * (with its real category/duration/price), the exact same source and
     * grouping the calendar side panel's service picker uses — not from
     * ProductService, which is only the accounting-side mirror of it (see
     * ServiceController::store()). Parts has no such booking-side catalog,
     * so it reads directly from ProductService's own `type = 'product'` rows.
     *
     * Each item also carries the same `tax_id` ProductService stores (a
     * single id or a comma-separated list, per Invoice::totalTaxRate()) so
     * the form's live Payment Summary can replicate that model method
     * client-side without a round trip.
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
            'taxInclusive' => company_setting('service_tax_option') == 'inclusive',
            // Just enough for the live client-side preview to look right —
            // the actual saved invoice is always rendered server-side via
            // currency_format_with_sym() (modern-view.blade.php), so this
            // doesn't need to replicate its full formatting rules.
            'currencySymbol' => company_setting('defult_currancy_symbol') ?: '$',
            'currencySymbolPost' => company_setting('site_currency_symbol_position') == 'post',
            'customFields' => $this->customFieldDefinitions(),
        ];
    }

    /**
     * The business's custom-field definitions flagged for the invoice form —
     * same source and `custom_field_enable` gate as
     * BookingV2Controller::customFieldDefinitions(), just filtered by
     * `show_in_invoice` instead of `show_in_appointment`. Values land in
     * invoices.custom_field as a label => value JSON map, same shape as
     * appointments.custom_field.
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
            ->where('show_in_invoice', 1)
            ->orderBy('id')
            ->get(['label', 'type', 'value'])
            ->map(fn ($field) => ['label' => $field->label, 'type' => $field->type, 'value' => $field->value])
            ->values()
            ->all();
    }

    /**
     * Keep only values whose label matches a defined field, so the stored
     * JSON can't be stuffed with arbitrary keys from the client — same
     * safeguard BookingV2Controller::sanitizeCustomFields() applies.
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
    protected function saveItems(Invoice $invoice, array $items, $businessId): void
    {
        foreach ($items as $line) {
            if ($line['item_type'] === 'service') {
                $service = Service::where('id', $line['item_id'])
                    ->where('business_id', $businessId)
                    ->first();

                if (!$service) {
                    continue;
                }

                // Every real service already has a matching ProductService
                // row (ServiceController::store() creates it) — an invoice
                // line always ultimately references that accounting-side
                // catalogue, whichever picker it came from.
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

            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $catalogEntry->id;
            $invoiceProduct->product_type = $line['item_type'] === 'service' ? 'service' : 'product';
            $invoiceProduct->tax = $catalogEntry->tax_id;
            $invoiceProduct->discount = $line['discount'] ?? 0;
            $invoiceProduct->quantity = $line['quantity'];
            $invoiceProduct->price = $line['price'];
            $invoiceProduct->description = !empty($line['description']) ? $line['description'] : $fallbackName;
            $invoiceProduct->save();
        }
    }
}
