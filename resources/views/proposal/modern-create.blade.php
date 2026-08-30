@extends('layouts.main')

@php
    $isEdit = isset($proposal);
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
    {{ $isEdit ? __('Edit Quotation') : __('Create Quotation') }}
@endsection
@section('page-breadcrumb')
    {{ __('Quotation') }},{{ $isEdit ? __('Edit') : __('Create') }}
@endsection

@push('css')
<style>
    /* Same typeahead/theme styling as the modern invoice form
       (resources/views/invoice/modern-create.blade.php) — kept as its own
       copy since that page's <style> block is scoped to itself. */
    .modern-proposal-typeahead {
        position: relative;
    }

    .modern-proposal-ta-results {
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

    .modern-proposal-ta-results.show {
        display: block;
    }

    .modern-proposal-ta-item {
        padding: .4rem .6rem;
        cursor: pointer;
        border-bottom: 1px solid #f6f6f8;
    }

    .modern-proposal-ta-item:hover {
        background: #f8f9ff;
    }

    .modern-proposal-ta-name {
        font-weight: 600;
        font-size: .82rem;
    }

    .modern-proposal-ta-meta {
        color: #6b7280;
        font-size: .72rem;
    }

    .modern-proposal-customer-card {
        display: flex;
        gap: .6rem;
        align-items: center;
        background: #f8f9ff;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        padding: .5rem .65rem;
    }

    .modern-proposal-avatar {
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

    .modern-proposal-card-remove {
        background: none;
        border: 0;
        color: #6b7280;
        font-size: .75rem;
    }

    .modern-proposal-card-remove:hover {
        color: #dc2626;
    }

    /* Same premium indigo/purple accent theme as the modern invoice and
       booking calendar pages. */
    .modern-proposal-page .card {
        border: 1px solid #e0e7ff;
    }

    .modern-proposal-page .card-header {
        background: #eef2ff;
        border-bottom: 1px solid #e0e7ff;
    }

    .modern-proposal-page .card-header h6 {
        color: #4338ca;
        font-weight: 700;
    }

    .modern-proposal-page .table thead th {
        background: #f8f9ff;
        color: #4f46e5;
        text-transform: uppercase;
        font-size: .7rem;
        letter-spacing: .04em;
        border-bottom: 1px solid #e0e7ff;
    }

    .modern-proposal-total {
        background: linear-gradient(135deg, #eef2ff, #f5f3ff);
        border-radius: 8px;
        padding: .6rem .85rem;
    }

    .modern-proposal-total span {
        color: #4338ca;
    }
</style>
@endpush

@section('content')
    @if ($isEdit)
        {{ Form::open(['route' => ['modern-proposal.update', $e_id], 'method' => 'PUT', 'id' => 'modern-proposal-form']) }}
    @else
        {{ Form::open(['route' => 'modern-proposal.store', 'method' => 'POST', 'id' => 'modern-proposal-form']) }}
    @endif
    <div class="row g-3 modern-proposal-page">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ __('Quotation Details') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        @if ($isEdit)
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Quotation Number') }}</label>
                                <input type="text" class="form-control" value="{{ \Workdo\Invoice\Entities\Proposal::proposalNumberFormat($proposal->proposal_id, $proposal->created_by, $proposal->workspace ?? null) }}" readonly disabled>
                            </div>
                        @endif
                        <div class="{{ $isEdit ? 'col-md-5' : 'col-md-7' }}">
                            <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                            {{-- Same search-as-you-type customer picker as the modern
                                 invoice form and the calendar side panel's New
                                 Appointment form, backed by the exact same endpoint
                                 (bookings-v2.customers). --}}
                            <div id="modern-proposal-customer-selected" style="{{ $selectedCustomer ? '' : 'display:none' }}">
                                <div class="modern-proposal-customer-card">
                                    <div class="modern-proposal-avatar"><i class="ti ti-user"></i></div>
                                    <div>
                                        <div class="modern-proposal-ta-name" id="modern-proposal-customer-name">{{ $selectedCustomer['name'] ?? '' }}</div>
                                        <div class="modern-proposal-ta-meta" id="modern-proposal-customer-meta">{{ $selectedCustomer ? collect([$selectedCustomer['mobile'], $selectedCustomer['email']])->filter()->implode(' · ') : '' }}</div>
                                    </div>
                                    <button type="button" class="modern-proposal-card-remove ms-auto" id="modern-proposal-customer-change">{{ __('Change') }}</button>
                                </div>
                            </div>
                            <div class="modern-proposal-typeahead" id="modern-proposal-customer-search-wrap" style="{{ $selectedCustomer ? 'display:none' : '' }}">
                                <input type="text" class="form-control form-control-sm" id="modern-proposal-cust-input"
                                    placeholder="{{ __('Search name, mobile or email') }}" autocomplete="off">
                                <div class="modern-proposal-ta-results" id="modern-proposal-ta-results"></div>
                            </div>
                            <input type="hidden" name="customer_id" id="modern-proposal-customer-id"
                                value="{{ $selectedCustomer['id'] ?? '' }}" required>
                        </div>
                        <div class="{{ $isEdit ? 'col-md-4' : 'col-md-5' }}">
                            <label class="form-label">{{ __('Issue Date') }} <span class="text-danger">*</span></label>
                            {{ Form::date('issue_date', $isEdit ? $proposal->issue_date : date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        {{-- Business-defined fields flagged "Show in Quotation"
                             (Business > Custom Field tab) — same storage
                             shape as the booking panel's own custom fields
                             (a label => value JSON map), just on the
                             quotation row instead of the appointment row. --}}
                        @foreach ($customFields as $field)
                            @php
                                $fieldValue = old('custom_field.' . $field['label'], $existingCustomFieldValues[$field['label']] ?? $field['value'] ?? '');
                                $fieldId = 'modern-proposal-custom-' . \Illuminate\Support\Str::slug($field['label']);
                            @endphp
                            <div class="col-md-4">
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
                    <button type="button" class="btn btn-sm btn-primary" id="modern-proposal-add-row">
                        <i class="ti ti-plus me-1"></i>{{ __('Add Item') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="modern-proposal-items-table">
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
                            <tbody id="modern-proposal-items-body"></tbody>
                        </table>
                    </div>
                    <div class="p-3 text-muted small" id="modern-proposal-no-items">{{ __('Add at least one item.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Notes') }}</h6></div>
                <div class="card-body">
                    <textarea name="notes" id="modern-proposal-notes" class="form-control" rows="6"
                        maxlength="2000" placeholder="{{ __('Add a comment or note about this quotation…') }}">{{ old('notes', $notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Quote Summary') }}</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Subtotal') }}</span>
                        <span id="modern-proposal-sum-subtotal"></span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Discount') }}</span>
                        <span id="modern-proposal-sum-discount"></span>
                    </div>
                    <div class="d-flex justify-content-between py-1" id="modern-proposal-sum-tax-row" style="display:none">
                        <span class="text-muted">{{ __('Tax') }}</span>
                        <span id="modern-proposal-sum-tax"></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between py-1 fw-semibold fs-5 modern-proposal-total">
                        <span>{{ __('Total') }}</span>
                        <span id="modern-proposal-sum-total"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end">
            <a href="{{ route('proposal.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Save Changes') : __('Create Quotation') }}</button>
        </div>
    </div>
    {{ Form::close() }}
@endsection

@php
    // Pulled out of the @json() call below for the same reason
    // modern-invoice-create.blade.php does: Blade's directive-argument
    // scanner miscounts brackets on a multi-key array literal built inside
    // an arrow function passed straight to @json(...), silently truncating
    // the argument — a plain variable avoids it.
    $partsForJs = $parts->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => $p->sale_price,
        'tax_id' => $p->tax_id,
    ])->values();
@endphp

@push('scripts')
<script>
    // Customer search — identical mechanics to the modern invoice form's own
    // copy of this (and booking-v2.blade.php's bindCustomerTypeahead()):
    // debounced input, the same bookings-v2.customers endpoint, same 2-char
    // minimum, click a result to select.
    (function () {
        var input = document.getElementById('modern-proposal-cust-input');
        var results = document.getElementById('modern-proposal-ta-results');
        var hiddenId = document.getElementById('modern-proposal-customer-id');
        var selectedBox = document.getElementById('modern-proposal-customer-selected');
        var searchWrap = document.getElementById('modern-proposal-customer-search-wrap');
        var nameEl = document.getElementById('modern-proposal-customer-name');
        var metaEl = document.getElementById('modern-proposal-customer-meta');
        var changeBtn = document.getElementById('modern-proposal-customer-change');

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
                            results.innerHTML = '<div class="modern-proposal-ta-item text-muted">' +
                                @json(__('No customers found.')) + '</div>';
                            results.classList.add('show');
                            return;
                        }

                        results.innerHTML = rows.map(function (row) {
                            return '<div class="modern-proposal-ta-item" data-id="' + esc(row.id) +
                                '" data-name="' + esc(row.name) + '" data-mobile="' + esc(row.mobile) +
                                '" data-email="' + esc(row.email) + '">' +
                                '<div class="modern-proposal-ta-name">' + esc(row.name) + '</div>' +
                                '<div class="modern-proposal-ta-meta">' +
                                esc([row.mobile, row.email].filter(Boolean).join(' · ')) +
                                '</div></div>';
                        }).join('');

                        results.classList.add('show');

                        results.querySelectorAll('.modern-proposal-ta-item[data-id]').forEach(function (item) {
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
        // Service: grouped by category. Parts: a flat list — ProductService's
        // own `type = 'product'` catalogue. Identical source to the modern
        // invoice form, since a quotation's items land in the same catalogue.
        var serviceGroups = @json($serviceGroups);
        var parts = @json($partsForJs);

        // Same values Proposal::getSubTotal()/getTotalTax()/getTotal() use
        // server-side (Proposal::totalTaxRate() sums a comma-separated list
        // of Tax ids) — replicated here so the summary below updates live,
        // without a round trip, as rows are added/edited/removed. Unlike
        // Invoice, Proposal::getTotal() has no tax-inclusive branch — tax is
        // always added on top.
        var taxRates = @json($taxRates);
        var currencySymbol = @json($currencySymbol);
        var currencySymbolPost = @json($currencySymbolPost);

        var body = document.getElementById('modern-proposal-items-body');
        var noItems = document.getElementById('modern-proposal-no-items');
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

            var total = subtotal - discount + tax;

            document.getElementById('modern-proposal-sum-subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('modern-proposal-sum-discount').textContent = discount > 0 ? ('-' + formatCurrency(discount)) : formatCurrency(0);
            document.getElementById('modern-proposal-sum-tax').textContent = formatCurrency(tax);
            document.getElementById('modern-proposal-sum-tax-row').style.display = tax > 0 ? '' : 'none';
            document.getElementById('modern-proposal-sum-total').textContent = formatCurrency(total);
        }

        function bindItemSelect(row) {
            var select = row.querySelector('[data-act="item"]');

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
                $(itemSelect).select2('destroy');
                itemSelect.innerHTML = itemOptionsHtml(this.value);
                $(itemSelect).select2({ width: '100%', placeholder: @json(__('Select')) });
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

        document.getElementById('modern-proposal-add-row').addEventListener('click', function () { addRow(); });

        body.addEventListener('input', recalcSummary);
        body.addEventListener('change', recalcSummary);

        document.getElementById('modern-proposal-form').addEventListener('submit', function (e) {
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
