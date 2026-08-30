@extends('layouts.main')

@php
    // Pilot: display only. Every value below comes straight off the existing
    // Invoice/InvoiceProduct/InvoicePayment models and their own computed
    // totals (getSubTotal()/getTotal()/getDue()) — nothing here recalculates
    // or stores anything differently than the original invoice::invoice.view.
    $statusLabel = \Workdo\Invoice\Entities\Invoice::$statues[$invoice->status] ?? __('Unknown');
    $statusClass = [
        0 => 'secondary', // Draft
        1 => 'info',      // Sent
        2 => 'danger',    // Unpaid
        3 => 'warning',   // Partialy Paid
        4 => 'success',   // Paid
    ][$invoice->status] ?? 'secondary';

    $invoiceNumber = \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace ?? null);
    $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($invoice->id);
@endphp

@section('page-title')
    {{ __('Invoice') }} {{ $invoiceNumber }}
@endsection
@section('page-breadcrumb')
    {{ __('Invoice Detail') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        @permission('invoice edit')
            <a href="{{ route('invoice.edit', $encryptedId) }}" class="btn btn-sm btn-light"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil"></i>
            </a>
        @endpermission
        <a href="{{ route('invoice.pdf', $encryptedId) }}" target="_blank" class="btn btn-sm btn-outline-primary"
            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Download PDF') }}">
            <i class="ti ti-download"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" id="modern-invoice-copy-link"
            data-link="{{ route('pay.invoice', $encryptedId) }}"
            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Copy Payment Link') }}">
            <i class="ti ti-link"></i>
        </a>
        {{-- Moved here from the invoice list's row actions, which now uses
             that slot for Issue Credit Note instead. --}}
        @permission('invoice duplicate')
            {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' => 'duplicate-form-' . $invoice->id, 'class' => 'd-inline m-0']) !!}
                <a href="#" class="btn btn-sm btn-light bs-pass-para show_confirm" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-original-title="{{ __('Duplicate') }}"
                    data-text="{{ __('You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back') }}"
                    data-confirm-yes="duplicate-form-{{ $invoice->id }}">
                    <i class="ti ti-copy"></i>
                </a>
            {!! Form::close() !!}
        @endpermission
        {{-- This is a full page, not a modal, so there's nothing to
             literally "close" — this just returns to the invoice list,
             for anyone who lands here without an obvious way back. --}}
        <a href="{{ route('invoice.index') }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip"
            data-bs-placement="top" data-bs-original-title="{{ __('Back to Invoices') }}">
            <i class="ti ti-x"></i>
        </a>
    </div>
@endsection

@push('css')
<style>
    /* Same indigo/purple accent theme as the booking calendar's side panel
       (booking-v2.blade.php) and the modern invoice create/edit form, so all
       three read as one visual family. */
    .modern-invoice-page .card {
        border: 1px solid #e0e7ff;
    }

    .modern-invoice-page .card-header {
        background: #eef2ff;
        border-bottom: 1px solid #e0e7ff;
        /* The theme's default card-header padding (25px top/bottom) made
           every section title as tall as a whole row of table data —
           tightened to match the 14px body text below it instead. */
        padding: .6rem 1rem;
    }

    .modern-invoice-page .card-header h6 {
        color: #4338ca;
        font-weight: 700;
        font-size: .875rem;
        line-height: 1.3;
    }

    .modern-invoice-page .table thead th {
        background: #f8f9ff;
        color: #4f46e5;
        text-transform: uppercase;
        font-size: .7rem;
        letter-spacing: .04em;
        border-bottom: 1px solid #e0e7ff;
    }

    .modern-invoice-hero {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border: none;
        color: #fff;
    }

    .modern-invoice-hero .text-muted {
        color: rgba(255, 255, 255, .78) !important;
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
    <div class="row g-3 modern-invoice-page">
        {{-- Header --}}
        <div class="col-12">
            <div class="card modern-invoice-hero">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="mb-1">{{ $invoiceNumber }}</h4>
                        <div class="text-muted small">
                            {{ __('Issued') }} {{ company_date_formate($invoice->issue_date, $invoice->created_by, $invoice->workspace ?? null) }}
                            @if ($invoice->due_date)
                                &nbsp;·&nbsp; {{ __('Due') }} {{ company_date_formate($invoice->due_date, $invoice->created_by, $invoice->workspace ?? null) }}
                            @endif
                        </div>
                        @if ($invoice->proposal_id)
                            @php
                                $sourceProposal = \Workdo\Invoice\Entities\Proposal::find($invoice->proposal_id);
                            @endphp
                            @if ($sourceProposal)
                                <div class="text-muted small mt-1">
                                    <i class="ti ti-file-text me-1"></i>{{ __('Converted from Quotation') }}
                                    @permission('proposal show')
                                        <a href="{{ route('proposal.show', \Illuminate\Support\Facades\Crypt::encrypt($sourceProposal->id)) }}" style="color: inherit; text-decoration: underline;">
                                            {{ \Workdo\Invoice\Entities\Proposal::proposalNumberFormat($sourceProposal->proposal_id, $sourceProposal->created_by, $sourceProposal->workspace ?? null) }}
                                        </a>
                                    @else
                                        {{ \Workdo\Invoice\Entities\Proposal::proposalNumberFormat($sourceProposal->proposal_id, $sourceProposal->created_by, $sourceProposal->workspace ?? null) }}
                                    @endpermission
                                </div>
                            @endif
                        @endif
                    </div>
                    <span class="badge bg-{{ $statusClass }} px-3 py-2 fs-6">{{ __($statusLabel) }}</span>
                </div>
            </div>
        </div>

        {{-- Bill to / summary --}}
        @php
            // Business-defined fields (Business > Custom Field tab, "Show in
            // Invoice") — same label => value JSON map shape stored on
            // appointments.custom_field, just read from invoices.custom_field.
            // Folded into the Bill To card (below) instead of their own
            // full-width card, since there are normally only one or two of
            // them (e.g. Rego, Make & Model) and giving them a whole row was
            // mostly empty space.
            $invoiceCustomFieldValues = array_filter((array) json_decode($invoice->custom_field ?? '', true) ?: []);
        @endphp
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Bill To') }}</h6></div>
                <div class="card-body">
                    @if ($invoice->customer)
                        <div class="fw-semibold mb-1">{{ $invoice->customer->name }}</div>
                        @if ($invoice->customer->email)
                            <div class="text-muted small"><i class="ti ti-mail me-1"></i>{{ $invoice->customer->email }}</div>
                        @endif
                        @if ($invoice->customer->mobile_no)
                            <div class="text-muted small"><i class="ti ti-phone me-1"></i>{{ $invoice->customer->mobile_no }}</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('No customer on file.') }}</p>
                    @endif

                    @if (!empty($invoiceCustomFieldValues))
                        <hr class="my-2">
                        <div class="row g-2">
                            @foreach ($invoiceCustomFieldValues as $label => $value)
                                <div class="col-6">
                                    <div class="text-muted small">{{ $label }}</div>
                                    <div class="fw-semibold">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Summary') }}</h6></div>
                <div class="card-body">
                    @php
                        // getTotal() itself branches on this setting (see the
                        // Invoice model) — inclusive means tax is already
                        // folded into the prices above, not added on top, so
                        // the label has to say that rather than list it as if
                        // it were additive like Discount.
                        $taxInclusive = company_setting('service_tax_option') == 'inclusive';
                    @endphp
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Subtotal') }}</span>
                        <span>{{ currency_format_with_sym($invoice->getSubTotal()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Discount') }}</span>
                        <span>{{ $invoice->getTotalDiscount() > 0 ? '-' . currency_format_with_sym($invoice->getTotalDiscount()) : currency_format_with_sym(0) }}</span>
                    </div>
                    @if ($invoice->getTotalTax() > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">{{ $taxInclusive ? __('Tax (included)') : __('Tax') }}</span>
                            <span>{{ currency_format_with_sym($invoice->getTotalTax()) }}</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between py-1 fw-semibold fs-5 modern-invoice-payable">
                        <span>{{ __('Total') }}</span>
                        <span>{{ currency_format_with_sym($invoice->getTotal()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Paid') }}</span>
                        <span class="text-success">{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote()) }}</span>
                    </div>
                    {{-- invoiceTotalCustomerCreditNote() — not invoiceTotalCreditNote(),
                         a different, unrelated (and unused) entity. This is
                         informational only: a credit note issued here is
                         store credit for a *future* invoice, so it doesn't
                         reduce this invoice's own Paid/Balance Due above. --}}
                    @if ($invoice->invoiceTotalCustomerCreditNote() > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">{{ __('Credit Note Issued') }}</span>
                            <span>{{ currency_format_with_sym($invoice->invoiceTotalCustomerCreditNote()) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-1 fw-semibold">
                        <span>{{ __('Balance Due') }}</span>
                        <span class="{{ $invoice->getDue() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ currency_format_with_sym($invoice->getDue()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Line items --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ __('Items') }}</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Price') }}</th>
                                    <th class="text-end">{{ __('Discount') }}</th>
                                    <th class="text-end">{{ __('Tax') }}</th>
                                    <th class="text-end">{{ __('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($iteams as $item)
                                    @php
                                        $lineTotal = ($item->price * $item->quantity) - $item->discount;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product_name ?: $item->description }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ currency_format_with_sym($item->price) }}</td>
                                        <td class="text-end">{{ $item->discount ? currency_format_with_sym($item->discount) : '-' }}</td>
                                        <td class="text-end">{{ $item->tax ?: '-' }}</td>
                                        <td class="text-end">{{ currency_format_with_sym($lineTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">{{ __('No items on this invoice.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payments — history and the record-payment form share one tile,
             since they're really one topic (money received on this
             invoice) rather than two independent sections. --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ __('Payments') }}</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    @permission('invoice payment delete')
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endpermission
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ company_date_formate($payment->date, $invoice->created_by, $invoice->workspace ?? null) }}</td>
                                        <td>{{ $payment->reference ?: $payment->description ?: '-' }}</td>
                                        <td class="text-end">{{ currency_format_with_sym($payment->amount) }}</td>
                                        @permission('invoice payment delete')
                                            <td class="text-end">
                                                {{-- Deleting a payment is the only way to get a paid/partially-paid
                                                     invoice back to a state InvoiceController::destroy() will allow
                                                     deleting — it now rejects an invoice with any payment recorded. --}}
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-payment-delete="{{ route('invoice.payment.destroy', [$invoice->id, $payment->id]) }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        @endpermission
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">{{ __('No payments recorded yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Record payment — same endpoint (invoice.payment.store)
                         and the same InvoicePayType catalogue the original
                         screen uses, so a payment recorded here is identical
                         in every respect to one recorded from the original
                         page. --}}
                    @if ($invoice->getDue() > 0)
                        @permission('invoice payment create')
                            <div class="p-3 border-top">
                                {{ Form::open(['route' => ['invoice.payment.store', $invoice->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Date') }}</label>
                                        {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Amount') }}</label>
                                        {{ Form::number('amount', number_format($invoice->getDue(), 2, '.', ''), ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required', 'id' => 'modern-invoice-payment-amount']) }}
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Payment Type') }}</label>
                                        {{ Form::select('payment_type', $payTypes->pluck('name', 'id'), null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select'), 'id' => 'modern-invoice-payment-type']) }}
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Reference') }}</label>
                                        {{ Form::text('reference', null, ['class' => 'form-control']) }}
                                    </div>
                                    {{-- Only ever populated (and so only ever
                                         visible) when the invoice's customer
                                         is eligible — see
                                         InvoiceController::show()'s modern
                                         branch. Picking "Credit Note" above
                                         reveals this and caps Amount to
                                         whichever note is selected. --}}
                                    @if ($availableCreditNotes->isNotEmpty())
                                        <div class="col-md-6" id="modern-invoice-credit-note-wrap" style="display:none">
                                            <label class="form-label">{{ __('Credit Note') }}</label>
                                            <select name="credit_note_id" id="modern-invoice-credit-note-select" class="form-control">
                                                @foreach ($availableCreditNotes as $note)
                                                    <option value="{{ $note->id }}" data-remaining="{{ $note->remaining_amount }}">
                                                        #{{ $note->id }} — {{ currency_format_with_sym($note->remaining_amount) }} {{ __('available') }} ({{ company_date_formate($note->date, $invoice->created_by, $invoice->workspace ?? null) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">{{ __('Record Payment') }}</button>
                                </div>
                                {{ Form::close() }}
                            </div>
                        @endpermission
                    @endif
                </div>
            </div>
        </div>

        {{-- Notes + Attachments — both low-traffic sections, so they share a
             row rather than each claiming the full width. --}}
        @if (!empty($invoice->notes))
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">{{ __('Notes') }}</h6></div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $invoice->notes }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Attachments — same storage (InvoiceAttechment) and upload/delete
             endpoints as the original screen. --}}
        <div class="{{ empty($invoice->notes) ? 'col-12' : 'col-md-6' }}">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Attachments') }}</h6></div>
                <div class="card-body">
                    @forelse ($invoice_attachment as $file)
                        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2" data-attachment-row="{{ $file->id }}">
                            <a href="{{ check_file($file->file_path) ? get_file($file->file_path) : '#' }}" target="_blank" class="text-truncate me-2">
                                <i class="ti ti-paperclip me-1"></i>{{ $file->file_name }}
                                <span class="text-muted small">({{ $file->file_size }})</span>
                            </a>
                            @permission('invoice edit')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-attachment-delete="{{ route('invoice.attachment.destroy', $file->id) }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endpermission
                        </div>
                    @empty
                        <p class="text-muted small mb-3" id="modern-invoice-no-attachments">{{ __('No attachments yet.') }}</p>
                    @endforelse

                    @permission('invoice edit')
                        <div class="input-group input-group-sm">
                            <input type="file" class="form-control" id="modern-invoice-attachment-input">
                            <button type="button" class="btn btn-outline-primary" id="modern-invoice-attachment-upload">
                                <i class="ti ti-upload me-1"></i>{{ __('Upload') }}
                            </button>
                        </div>
                    @endpermission
                </div>
            </div>
        </div>

        {{-- Bottom-of-page reminder that this invoice has store credit
             attached to it, regardless of its own paid/due status (a credit
             note is money the customer can use on a *future* invoice, so it
             doesn't otherwise show up as "unfinished business" on this one). --}}
        @if ($invoice->invoiceTotalCustomerCreditNote() > 0)
            <div class="col-12">
                <div class="alert alert-info mb-0 d-flex align-items-center">
                    <i class="ti ti-info-circle me-2"></i>
                    {{ __('This invoice also has a credit note of amount :amount.', ['amount' => currency_format_with_sym($invoice->invoiceTotalCustomerCreditNote())]) }}
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('modern-invoice-copy-link').addEventListener('click', function (e) {
        e.preventDefault();
        var temp = document.createElement('input');
        document.body.appendChild(temp);
        temp.value = this.getAttribute('data-link');
        temp.select();
        document.execCommand('copy');
        temp.remove();
        if (typeof toastrs === 'function') {
            toastrs('{{ __('Success') }}', '{{ __('Link copied to clipboard') }}', 'success');
        }
    });

    (function () {
        var uploadBtn = document.getElementById('modern-invoice-attachment-upload');
        var fileInput = document.getElementById('modern-invoice-attachment-input');

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', function () {
                if (!fileInput.files.length) {
                    return;
                }

                var formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                uploadBtn.disabled = true;

                fetch(@json(route('invoice.file.upload', $invoice->id)), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.is_success) {
                            // Same reason as the job-card panel: the server is the
                            // source of truth for file size/name formatting, so a
                            // full reload is simpler and more correct than trying
                            // to duplicate that formatting here.
                            location.reload();
                        } else {
                            toastrs('{{ __('Error') }}', data.error || '{{ __('Upload failed.') }}', 'error');
                            uploadBtn.disabled = false;
                        }
                    })
                    .catch(function () {
                        toastrs('{{ __('Error') }}', '{{ __('Upload failed.') }}', 'error');
                        uploadBtn.disabled = false;
                    });
            });
        }

        document.querySelectorAll('[data-attachment-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('{{ __('Delete this attachment?') }}')) {
                    return;
                }

                fetch(btn.getAttribute('data-attachment-delete'), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(function () {
                    location.reload();
                });
            });
        });

        document.querySelectorAll('[data-payment-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('{{ __('Delete this payment? This cannot be undone.') }}')) {
                    return;
                }

                // invoice.payment.destroy is a plain POST route, not DELETE.
                fetch(btn.getAttribute('data-payment-delete'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(function () {
                    location.reload();
                });
            });
        });
    })();

    // Store credit as a payment method: picking "Credit Note" reveals which
    // note to redeem and caps Amount to its remaining balance — the actual
    // eligibility/redemption rules live server-side
    // (InvoiceController::createPayment()); this is display convenience only.
    (function () {
        var paymentType = document.getElementById('modern-invoice-payment-type');
        var creditWrap = document.getElementById('modern-invoice-credit-note-wrap');
        var creditSelect = document.getElementById('modern-invoice-credit-note-select');
        var amountInput = document.getElementById('modern-invoice-payment-amount');

        if (!paymentType || !creditWrap || !creditSelect || !amountInput) {
            return;
        }

        function isCreditNoteSelected() {
            var option = paymentType.options[paymentType.selectedIndex];
            return !!option && option.text === 'Credit Note';
        }

        function clampAmountToCreditNote() {
            var option = creditSelect.options[creditSelect.selectedIndex];
            var remaining = option ? parseFloat(option.getAttribute('data-remaining')) : null;

            if (remaining !== null && !isNaN(remaining)) {
                amountInput.setAttribute('max', remaining);
                if (parseFloat(amountInput.value) > remaining) {
                    amountInput.value = remaining.toFixed(2);
                }
            }
        }

        paymentType.addEventListener('change', function () {
            if (isCreditNoteSelected()) {
                creditWrap.style.display = '';
                clampAmountToCreditNote();
            } else {
                creditWrap.style.display = 'none';
                amountInput.removeAttribute('max');
            }
        });

        creditSelect.addEventListener('change', clampAmountToCreditNote);
    })();
</script>
@endpush
