@extends('layouts.main')

@php
    $isEdit = isset($invoice);
    $selectedCustomer = $selectedCustomer ?? null;
    $existingItemsForJs = $isEdit
        ? $items->map(fn ($item) => [
            'item_type' => $item['item_type'],
            'item_id' => $item['item_id'],
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'discount' => $item['discount'],
        ])->values()
        : [];
@endphp

@section('page-title')
    {{ $isEdit ? __('Edit Invoice') : __('Create Invoice') }}
@endsection
@section('page-breadcrumb')
    {{ __('Invoice') }},{{ $isEdit ? __('Edit') : __('Create') }}
@endsection

@push('css')
<style>
    /* Copied from the booking calendar's own customer typeahead
       (resources/views/appointment/booking-v2.blade.php) so this looks and
       behaves identically — that page's CSS is scoped to itself and isn't
       available here otherwise. */
    .modern-invoice-typeahead {
        position: relative;
    }

    .modern-invoice-ta-results {
        position: absolute;
        z-index: 5;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .1);
        max-height: 220px;
        overflow-y: auto;
        display: none;
    }

    .modern-invoice-ta-results.show {
        display: block;
    }

    .modern-invoice-ta-item {
        padding: .4rem .6rem;
        cursor: pointer;
        border-bottom: 1px solid #f6f6f8;
    }

    .modern-invoice-ta-item:hover {
        background: #f8f9ff;
    }

    .modern-invoice-ta-name {
        font-weight: 600;
        font-size: .82rem;
    }

    .modern-invoice-ta-meta {
        color: #6b7280;
        font-size: .72rem;
    }

    .modern-invoice-customer-card {
        display: flex;
        gap: .6rem;
        align-items: center;
        background: #f8f9ff;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        padding: .5rem .65rem;
    }

    .modern-invoice-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #e0e7ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
    }

    .modern-invoice-card-remove {
        background: none;
        border: 0;
        color: #6b7280;
        font-size: .75rem;
    }

    .modern-invoice-card-remove:hover {
        color: #dc2626;
    }

    /* Premium accent theme — same indigo/purple the booking calendar's side
       panel (booking-v2.blade.php) already uses, so this pilot's forms and
       the appointment panel read as one visual family. */
    .modern-invoice-page .card {
        border: 1px solid #e0e7ff;
    }

    .modern-invoice-page .card-header {
        background: #eef2ff;
        border-bottom: 1px solid #e0e7ff;
    }

    .modern-invoice-page .card-header h6 {
        color: #4338ca;
        font-weight: 700;
    }

    .modern-invoice-page .table thead th {
        background: #f8f9ff;
        color: #4f46e5;
        text-transform: uppercase;
        font-size: .7rem;
        letter-spacing: .04em;
        border-bottom: 1px solid #e0e7ff;
    }

    .modern-invoice-payable {
        background: linear-gradient(135deg, #eef2ff, #f5f3ff);
        border-radius: 8px;
        padding: .6rem .85rem;
    }

    .modern-invoice-payable span {
        color: #4338ca;
    }
</style>
@endpush

@section('content')
    @if ($isEdit)
        {{ Form::open(['route' => ['modern-invoice.update', $e_id], 'method' => 'PUT', 'id' => 'modern-invoice-form']) }}
    @else
        {{ Form::open(['route' => 'modern-invoice.store', 'method' => 'POST', 'id' => 'modern-invoice-form']) }}
    @endif
    <div class="row g-3 modern-invoice-page">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ __('Invoice Details') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        @if ($isEdit)
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Invoice Number') }}</label>
                                <input type="text" class="form-control" value="{{ \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace ?? null) }}" readonly disabled>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                            {{-- Same search-as-you-type customer picker as the calendar
                                 side panel's New Appointment form, backed by the exact
                                 same endpoint (bookings-v2.customers). --}}
                            <div id="modern-invoice-customer-selected" style="{{ $selectedCustomer ? '' : 'display:none' }}">
                                <div class="modern-invoice-customer-card">
                                    <div class="modern-invoice-avatar"><i class="ti ti-user"></i></div>
                                    <div>
                                        <div class="modern-invoice-ta-name" id="modern-invoice-customer-name">{{ $selectedCustomer['name'] ?? '' }}</div>
                                        <div class="modern-invoice-ta-meta" id="modern-invoice-customer-meta">{{ $selectedCustomer ? collect([$selectedCustomer['mobile'], $selectedCustomer['email']])->filter()->implode(' · ') : '' }}</div>
                                    </div>
                                    <button type="button" class="modern-invoice-card-remove ms-auto" id="modern-invoice-customer-change">{{ __('Change') }}</button>
                                </div>
                            </div>
                            <div class="modern-invoice-typeahead" id="modern-invoice-customer-search-wrap" style="{{ $selectedCustomer ? 'display:none' : '' }}">
                                <input type="text" class="form-control form-control-sm" id="modern-invoice-cust-input"
                                    placeholder="{{ __('Search name, mobile or email') }}" autocomplete="off">
                                <div class="modern-invoice-ta-results" id="modern-invoice-ta-results"></div>
                            </div>
                            <input type="hidden" name="customer_id" id="modern-invoice-customer-id"
                                value="{{ $selectedCustomer['id'] ?? '' }}" required>
                        </div>
                        <div class="{{ $isEdit ? 'col-md-3' : 'col-md-4' }}">
                            <label class="form-label">{{ __('Issue Date') }} <span class="text-danger">*</span></label>
                            {{ Form::date('issue_date', $isEdit ? $invoice->issue_date : date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="{{ $isEdit ? 'col-md-3' : 'col-md-4' }}">
                            <label class="form-label">{{ __('Due Date') }} <span class="text-danger">*</span></label>
                            {{ Form::date('due_date', $isEdit ? $invoice->due_date : date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        {{-- Business-defined fields flagged "Show in Invoice"
                             (Business > Custom Field tab) — same storage
                             shape as the booking panel's own custom fields
                             (a label => value JSON map), just on the invoice
                             row instead of the appointment row.

                             Lined up under Issue Date / Due Date rather than
                             flowing left — the first field sits directly
                             below Issue Date, the second below Due Date,
                             using the same column width those two use so the
                             edges match exactly. A third field (or more)
                             simply wraps to a fresh row underneath, still
                             matching that same width. --}}
                        @php
                            $customFieldColWidth = $isEdit ? 'col-md-3' : 'col-md-4';
                            // Invoice Number (edit only, 2 cols) + Customer
                            // (4 cols) precede Issue Date — skip exactly that
                            // much so the first field starts at Issue Date's
                            // own left edge.
                            $customFieldLeadOffset = $isEdit ? 'offset-md-6' : 'offset-md-4';
                        @endphp
                        @foreach ($customFields as $field)
                            @php
                                $fieldValue = old('custom_field.' . $field['label'], $existingCustomFieldValues[$field['label']] ?? $field['value'] ?? '');
                                $fieldId = 'modern-invoice-custom-' . \Illuminate\Support\Str::slug($field['label']);
                            @endphp
                            <div class="{{ $customFieldColWidth }} {{ $loop->first ? $customFieldLeadOffset : '' }}">
                                <label class="form-label" for="{{ $fieldId }}">{{ $field['label'] }}</label>
                                @if ($field['type'] === 'textarea')
                                    <textarea name="custom_field[{{ $field['label'] }}]" id="{{ $fieldId }}" class="form-control" rows="2">{{ $fieldValue }}</textarea>
                                @else
                                    <input type="{{ $field['type'] === 'date' ? 'date' : ($field['type'] === 'number' ? 'number' : 'text') }}"
                                        name="custom_field[{{ $field['label'] }}]" id="{{ $fieldId }}" class="form-control"
                                        value="{{ $fieldValue }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Items') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="modern-invoice-add-row">
                        <i class="ti ti-plus me-1"></i>{{ __('Add Item') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="modern-invoice-items-table">
                            <thead>
                                <tr>
                                    <th style="min-width:110px">{{ __('Item Type') }}</th>
                                    <th style="min-width:220px">{{ __('Service') }}</th>
                                    <th style="min-width:180px">{{ __('Description') }}</th>
                                    <th style="width:100px">{{ __('Qty') }}</th>
                                    <th style="width:130px">{{ __('Price') }}</th>
                                    <th style="width:130px">{{ __('Discount') }}</th>
                                    <th style="width:60px"></th>
                                </tr>
                            </thead>
                            <tbody id="modern-invoice-items-body"></tbody>
                        </table>
                    </div>
                    <div class="p-3 text-muted small" id="modern-invoice-no-items">{{ __('Add at least one item.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Notes') }}</h6></div>
                <div class="card-body">
                    <textarea name="notes" id="modern-invoice-notes" class="form-control" rows="6"
                        maxlength="2000" placeholder="{{ __('Add a comment or note about this invoice…') }}">{{ old('notes', $notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Payment Summary') }}</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Subtotal') }}</span>
                        <span id="modern-invoice-sum-subtotal"></span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Discount') }}</span>
                        <span id="modern-invoice-sum-discount"></span>
                    </div>
                    <div class="d-flex justify-content-between py-1" id="modern-invoice-sum-tax-row" style="display:none">
                        <span class="text-muted">{{ $taxInclusive ? __('Tax (included)') : __('Tax') }}</span>
                        <span id="modern-invoice-sum-tax"></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between py-1 fw-semibold fs-5 modern-invoice-payable">
                        <span>{{ __('Payable') }}</span>
                        <span id="modern-invoice-sum-total"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end">
            <a href="{{ route('invoice.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Save Changes') : __('Create Invoice') }}</button>
        </div>
    </div>
    {{ Form::close() }}
@endsection

@php
    // Pulled out of the @json() call below: Blade's directive-argument
    // scanner miscounts brackets on a multi-key array literal built inside
    // an arrow function passed straight to @json(...), silently truncating
    // the argument (the same failure hit earlier in this file for
    // $existingItemsForJs) — a plain variable avoids it.
    $partsForJs = $parts->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => $p->sale_price,
        'tax_id' => $p->tax_id,
    ])->values();
@endphp

@push('scripts')
<script>
    // Customer search — identical mechanics to bindCustomerTypeahead() in
    // booking-v2.blade.php (New Appointment's customer field): debounced
    // input, the same bookings-v2.customers endpoint, same 2-char minimum,
    // click a result to select. Kept as its own IIFE since the calendar
    // panel's version is scoped to that page and not reachable from here.
    (function () {
        var input = document.getElementById('modern-invoice-cust-input');
        var results = document.getElementById('modern-invoice-ta-results');
        var hiddenId = document.getElementById('modern-invoice-customer-id');
        var selectedBox = document.getElementById('modern-invoice-customer-selected');
        var searchWrap = document.getElementById('modern-invoice-customer-search-wrap');
        var nameEl = document.getElementById('modern-invoice-customer-name');
        var metaEl = document.getElementById('modern-invoice-customer-meta');
        var changeBtn = document.getElementById('modern-invoice-customer-change');

        // Same helper booking-v2.blade.php's own typeahead uses — a customer
        // name landing in an HTML attribute (data-name="...") has to be
        // escaped there too, not just where it's later shown as text.
        function esc(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function selectCustomer(customer) {
            hiddenId.value = customer.id;
            nameEl.textContent = customer.name;
            metaEl.textContent = [customer.mobile, customer.email].filter(Boolean).join(' · ');
            selectedBox.style.display = '';
            searchWrap.style.display = 'none';
            results.classList.remove('show');
        }

        changeBtn.addEventListener('click', function () {
            hiddenId.value = '';
            selectedBox.style.display = 'none';
            searchWrap.style.display = '';
            input.value = '';
            input.focus();
        });

        var timer = null;

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var term = input.value.trim();

            if (term.length < 2) {
                results.classList.remove('show');
                return;
            }

            timer = setTimeout(function () {
                fetch(@json(route('bookings-v2.customers')) + '?q=' + encodeURIComponent(term), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        var rows = payload.results || [];

                        if (!rows.length) {
                            results.innerHTML = '<div class="modern-invoice-ta-item text-muted">' +
                                @json(__('No customers found.')) + '</div>';
                            results.classList.add('show');
                            return;
                        }

                        results.innerHTML = rows.map(function (row) {
                            return '<div class="modern-invoice-ta-item" data-id="' + esc(row.id) +
                                '" data-name="' + esc(row.name) + '" data-mobile="' + esc(row.mobile) +
                                '" data-email="' + esc(row.email) + '">' +
                                '<div class="modern-invoice-ta-name">' + esc(row.name) + '</div>' +
                                '<div class="modern-invoice-ta-meta">' +
                                esc([row.mobile, row.email].filter(Boolean).join(' · ')) +
                                '</div></div>';
                        }).join('');

                        results.classList.add('show');

                        results.querySelectorAll('.modern-invoice-ta-item[data-id]').forEach(function (item) {
                            item.addEventListener('click', function () {
                                selectCustomer({
                                    id: item.getAttribute('data-id'),
                                    name: item.getAttribute('data-name'),
                                    mobile: item.getAttribute('data-mobile'),
                                    email: item.getAttribute('data-email')
                                });
                            });
                        });
                    })
                    .catch(function () {
                        results.classList.remove('show');
                    });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!results.contains(e.target) && e.target !== input) {
                results.classList.remove('show');
            }
        });
    })();

    (function () {
        // Service: grouped by category, same source and structure as the
        // calendar side panel's own service picker (BookingV2Controller::formData()).
        // Parts: a flat list — ProductService's own `type = 'product'` catalogue,
        // which has no such booking-side category to group by.
        var serviceGroups = @json($serviceGroups);
        var parts = @json($partsForJs);

        // Same values Invoice::getSubTotal()/getTotalTax()/getTotal() use
        // server-side (Invoice::totalTaxRate() sums a comma-separated list of
        // Tax ids) — replicated here so the summary below updates live,
        // without a round trip, as rows are added/edited/removed.
        var taxRates = @json($taxRates);
        var taxInclusive = @json($taxInclusive);
        var currencySymbol = @json($currencySymbol);
        var currencySymbolPost = @json($currencySymbolPost);

        var body = document.getElementById('modern-invoice-items-body');
        var noItems = document.getElementById('modern-invoice-no-items');
        var rowIndex = 0;

        function itemOptionsHtml(itemType) {
            var html = '<option value="">' + @json(__('Select')) + '</option>';

            if (itemType === 'parts') {
                parts.forEach(function (part) {
                    html += '<option value="' + part.id + '" data-price="' + part.price + '" data-tax="' + (part.tax_id || '') + '">' + part.name + '</option>';
                });
                return html;
            }

            serviceGroups.forEach(function (group) {
                html += '<optgroup label="' + group.category + '">';
                group.items.forEach(function (item) {
                    html += '<option value="' + item.id + '" data-price="' + item.price + '" data-tax="' + (item.tax_id || '') + '">' + item.name + '</option>';
                });
                html += '</optgroup>';
            });
            return html;
        }

        function formatCurrency(value) {
            var formatted = (Math.round((value + Number.EPSILON) * 100) / 100).toFixed(2);
            return currencySymbolPost ? (formatted + currencySymbol) : (currencySymbol + formatted);
        }

        // taxIds may be a single id ("6") or a comma-separated list, exactly
        // like the tax column InvoiceProduct.tax / Invoice::totalTaxRate()
        // stores and reads.
        function taxRateForIds(taxIds) {
            if (!taxIds) {
                return 0;
            }
            return String(taxIds).split(',').reduce(function (sum, id) {
                id = id.trim();
                return sum + (id && taxRates[id] ? parseFloat(taxRates[id]) : 0);
            }, 0);
        }

        function recalcSummary() {
            var subtotal = 0;
            var discount = 0;
            var tax = 0;

            body.querySelectorAll('tr').forEach(function (row) {
                var qtyField = row.querySelector('[name$="[quantity]"]');
                var discountField = row.querySelector('[name$="[discount]"]');
                var priceField = row.querySelector('[data-act="price"]');
                var itemSelect = row.querySelector('[data-act="item"]');
                var option = itemSelect ? itemSelect.selectedOptions[0] : null;

                var qty = parseFloat(qtyField ? qtyField.value : 0) || 0;
                var price = parseFloat(priceField ? priceField.value : 0) || 0;
                var rowDiscount = parseFloat(discountField ? discountField.value : 0) || 0;
                var rate = option ? taxRateForIds(option.getAttribute('data-tax')) : 0;

                var lineSubtotal = qty * price;
                var lineTaxable = lineSubtotal - rowDiscount;

                subtotal += lineSubtotal;
                discount += rowDiscount;
                tax += lineTaxable > 0 ? (rate / 100) * lineTaxable : 0;
            });

            // Mirrors Invoice::getTotal()'s own branch: with inclusive
            // pricing, tax is already folded into the prices above rather
            // than added on top.
            var total = taxInclusive ? (subtotal - discount) : (subtotal - discount + tax);

            document.getElementById('modern-invoice-sum-subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('modern-invoice-sum-discount').textContent = discount > 0 ? ('-' + formatCurrency(discount)) : formatCurrency(0);
            document.getElementById('modern-invoice-sum-tax').textContent = formatCurrency(tax);
            document.getElementById('modern-invoice-sum-tax-row').style.display = tax > 0 ? '' : 'none';
            document.getElementById('modern-invoice-sum-total').textContent = formatCurrency(total);
        }

        function bindItemSelect(row) {
            var select = row.querySelector('[data-act="item"]');

            // Select2 over the plain <select> — searchable across every
            // service in every category (and across parts) instead of
            // scrolling a long grouped dropdown to find one by eye. No
            // dropdownParent override: this is a plain page (not a modal or
            // the booking panel's clipped side drawer), so Select2's default
            // of appending the dropdown to <body> already keeps it clear of
            // the table's own overflow-x scroll area.
            $(select).select2({
                width: '100%',
                placeholder: @json(__('Select'))
            });

            $(select).on('change', function () {
                var option = this.selectedOptions[0];
                var price = option ? option.getAttribute('data-price') : null;
                if (price !== null) {
                    row.querySelector('[data-act="price"]').value = price;
                }
            });
        }

        function addRow(prefill) {
            prefill = prefill || {};
            var itemType = prefill.item_type === 'parts' ? 'parts' : 'service';
            var index = rowIndex++;
            var row = document.createElement('tr');
            row.innerHTML =
                '<td><select class="form-control form-control-sm" name="items[' + index + '][item_type]" data-act="item_type">' +
                '<option value="service"' + (itemType === 'service' ? ' selected' : '') + '>' + @json(__('Service')) + '</option>' +
                '<option value="parts"' + (itemType === 'parts' ? ' selected' : '') + '>' + @json(__('Parts')) + '</option>' +
                '</select></td>' +
                '<td><select class="form-control form-control-sm" name="items[' + index + '][item_id]" data-act="item">' +
                itemOptionsHtml(itemType) + '</select></td>' +
                '<td><input type="text" class="form-control form-control-sm" name="items[' + index + '][description]" value="' + (prefill.description || '') + '"></td>' +
                '<td><input type="number" class="form-control form-control-sm" name="items[' + index + '][quantity]" value="' + (prefill.quantity || 1) + '" min="1" required></td>' +
                '<td><input type="number" class="form-control form-control-sm" name="items[' + index + '][price]" value="' + (prefill.price || 0) + '" min="0" step="0.01" data-act="price" required></td>' +
                '<td><input type="number" class="form-control form-control-sm" name="items[' + index + '][discount]" value="' + (prefill.discount || 0) + '" min="0" step="0.01"></td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger" data-act="remove"><i class="ti ti-trash"></i></button></td>';

            body.appendChild(row);

            if (prefill.item_id) {
                row.querySelector('[data-act="item"]').value = prefill.item_id;
            }

            updateEmptyState();
            bindItemSelect(row);

            row.querySelector('[data-act="item_type"]').addEventListener('change', function () {
                var itemSelect = row.querySelector('[data-act="item"]');
                // Select2 owns its own rendered copy of the <select> — swapping
                // innerHTML underneath it without destroying first leaves the
                // visible widget showing the old (now wrong) option list.
                $(itemSelect).select2('destroy');
                itemSelect.innerHTML = itemOptionsHtml(this.value);
                $(itemSelect).select2({ width: '100%', placeholder: @json(__('Select')) });
                // Switching type clears the selection (and its tax/price), so
                // the summary has to drop that row's old contribution too.
                recalcSummary();
            });

            row.querySelector('[data-act="remove"]').addEventListener('click', function () {
                row.remove();
                updateEmptyState();
                recalcSummary();
            });

            recalcSummary();
        }

        function updateEmptyState() {
            noItems.style.display = body.children.length ? 'none' : '';
        }

        document.getElementById('modern-invoice-add-row').addEventListener('click', function () { addRow(); });

        // Delegated on the tbody rather than per-field: covers every row's
        // qty/price/discount inputs as they're typed into, and the item
        // <select> itself (Select2 fires a real, bubbling native 'change' on
        // it), without needing a separate listener per row per field.
        body.addEventListener('input', recalcSummary);
        body.addEventListener('change', recalcSummary);

        document.getElementById('modern-invoice-form').addEventListener('submit', function (e) {
            if (!body.children.length) {
                e.preventDefault();
                alert(@json(__('Add at least one item.')));
            }
        });

        var existingItems = @json($existingItemsForJs);

        if (existingItems.length) {
            existingItems.forEach(function (item) { addRow(item); });
        } else {
            addRow();
        }
    })();
</script>
@endpush
