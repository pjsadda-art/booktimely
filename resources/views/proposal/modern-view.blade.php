@extends('layouts.main')

@php
    // Pilot: display only. Every value below comes straight off the existing
    // Proposal/ProposalProduct models and their own computed totals
    // (getSubTotal()/getTotal()) — nothing here recalculates or stores
    // anything differently than the original quotation::proposal.view.
    // Unlike an invoice, a quotation is never paid against directly, so
    // there's no Payments tile here at all.
    $statusLabel = \Workdo\Invoice\Entities\Proposal::$statues[$proposal->status] ?? __('Unknown');
    $statusClass = [
        0 => 'secondary', // Draft
        1 => 'info',      // Open
        2 => 'success',   // Accepted
        3 => 'danger',    // Declined
        4 => 'dark',      // Close
    ][$proposal->status] ?? 'secondary';

    $proposalNumber = \Workdo\Invoice\Entities\Proposal::proposalNumberFormat($proposal->proposal_id, $proposal->created_by, $proposal->workspace ?? null);
    $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($proposal->id);
@endphp

@section('page-title')
    {{ __('Quotation') }} {{ $proposalNumber }}
@endsection
@section('page-breadcrumb')
    {{ __('Quotation Detail') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        @permission('proposal edit')
            <a href="{{ route('proposal.edit', $encryptedId) }}" class="btn btn-sm btn-light"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil"></i>
            </a>
        @endpermission
        <a href="{{ route('proposal.pdf', $encryptedId) }}" target="_blank" class="btn btn-sm btn-outline-primary"
            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Download PDF') }}">
            <i class="ti ti-download"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" id="modern-proposal-copy-link"
            data-link="{{ route('pay.proposalpay', $encryptedId) }}"
            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Copy Quote Link') }}">
            <i class="ti ti-link"></i>
        </a>
        @php
            // Index 2 = Accepted (Proposal::$statues) — both conversions
            // below only make sense once the customer has actually accepted
            // the quotation, matching the gate ProposalController::convert()
            // and BookingV2Controller::proposalPrefill() enforce server-side.
            $isAccepted = (int) $proposal->status === 2;
        @endphp

        {{-- Convert to Appointment — hands the customer + line items to the
             booking calendar's own New Appointment panel
             (BookingV2Controller::proposalPrefill(), booking-v2.blade.php);
             staff still pick date/time/staff and save it themselves, same as
             any other new appointment. Independent of Convert to Invoice —
             a shop may reasonably want both from the same accepted quote. --}}
        @permission('appointment create')
            @if ($isAccepted)
                <a href="{{ route('bookings-v2.calendar') }}?convert_proposal={{ $encryptedId }}" class="btn btn-sm btn-outline-success"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Convert to Appointment') }}">
                    <i class="ti ti-calendar-plus"></i>
                </a>
            @else
                <a href="#" class="btn btn-sm btn-outline-success disabled" aria-disabled="true"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Only accepted quotations can be converted to an appointment.') }}">
                    <i class="ti ti-calendar-plus"></i>
                </a>
            @endif
        @endpermission

        {{-- Convert to Invoice — same endpoint (proposal.convert) and same
             one-way is_convert flag the original screen uses, so a
             conversion done here is identical in every respect to one done
             from the original page. --}}
        @if ($proposal->is_convert == 0)
            @permission('proposal convert invoice')
                @if ($isAccepted)
                    <a href="{{ route('proposal.convert', $proposal->id) }}" class="btn btn-sm btn-success"
                        onclick="return confirm({{ Js::from(__('This action can not be undone. Do you want to continue?')) }});"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Convert to Invoice') }}">
                        <i class="ti ti-exchange"></i>
                    </a>
                @else
                    <a href="#" class="btn btn-sm btn-success disabled" aria-disabled="true"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Only accepted quotations can be converted to an invoice.') }}">
                        <i class="ti ti-exchange"></i>
                    </a>
                @endif
            @endpermission
        @else
            @permission('invoice show')
                <a href="{{ route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($proposal->converted_invoice_id)) }}" class="btn btn-sm btn-success"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ __('Already converted — view Invoice') }}">
                    <i class="ti ti-eye"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection

@push('css')
<style>
    /* Same indigo/purple accent theme as the modern invoice view and the
       booking calendar's side panel, so all three read as one visual
       family. */
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

    .modern-proposal-hero {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border: none;
        color: #fff;
    }

    .modern-proposal-hero .text-muted {
        color: rgba(255, 255, 255, .78) !important;
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
    <div class="row g-3 modern-proposal-page">
        {{-- Header --}}
        <div class="col-12">
            <div class="card modern-proposal-hero">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="mb-1">{{ $proposalNumber }}</h4>
                        <div class="text-muted small">
                            {{ __('Issued') }} {{ company_date_formate($proposal->issue_date, $proposal->created_by, $proposal->workspace ?? null) }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $statusClass }} px-3 py-2 fs-6">{{ __($statusLabel) }}</span>
                </div>
            </div>
        </div>

        {{-- Bill to / summary --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Bill To') }}</h6></div>
                <div class="card-body">
                    @if ($proposal->customer)
                        <div class="fw-semibold mb-1">{{ $proposal->customer->name }}</div>
                        @if ($proposal->customer->email)
                            <div class="text-muted small"><i class="ti ti-mail me-1"></i>{{ $proposal->customer->email }}</div>
                        @endif
                        @if ($proposal->customer->mobile_no)
                            <div class="text-muted small"><i class="ti ti-phone me-1"></i>{{ $proposal->customer->mobile_no }}</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('No customer on file.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Quote Summary') }}</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Subtotal') }}</span>
                        <span>{{ currency_format_with_sym($proposal->getSubTotal()) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Discount') }}</span>
                        <span>{{ $proposal->getTotalDiscount() > 0 ? '-' . currency_format_with_sym($proposal->getTotalDiscount()) : currency_format_with_sym(0) }}</span>
                    </div>
                    @if ($proposal->getTotalTax() > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">{{ __('Tax') }}</span>
                            <span>{{ currency_format_with_sym($proposal->getTotalTax()) }}</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between py-1 fw-semibold fs-5 modern-proposal-total">
                        <span>{{ __('Total') }}</span>
                        <span>{{ currency_format_with_sym($proposal->getTotal()) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Business-defined fields (Business > Custom Field tab, "Show in
             Quotation") — same label => value JSON map shape stored on
             appointments.custom_field, just read from proposals.custom_field. --}}
        @php
            $proposalCustomFieldValues = array_filter((array) json_decode($proposal->custom_field ?? '', true) ?: []);
        @endphp
        @if (!empty($proposalCustomFieldValues))
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">{{ __('Details') }}</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($proposalCustomFieldValues as $label => $value)
                                <div class="col-md-4">
                                    <div class="text-muted small">{{ $label }}</div>
                                    <div class="fw-semibold">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
                                        <td>{{ $item->description }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ currency_format_with_sym($item->price) }}</td>
                                        <td class="text-end">{{ $item->discount ? currency_format_with_sym($item->discount) : '-' }}</td>
                                        <td class="text-end">{{ $item->tax ?: '-' }}</td>
                                        <td class="text-end">{{ currency_format_with_sym($lineTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">{{ __('No items on this quotation.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes + Attachments — both low-traffic sections, so they share a
             row rather than each claiming the full width. --}}
        @if (!empty($proposal->notes))
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">{{ __('Notes') }}</h6></div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $proposal->notes }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Attachments — same storage (ProposalAttechment) and upload/delete
             endpoints as the original screen. --}}
        <div class="{{ empty($proposal->notes) ? 'col-12' : 'col-md-6' }}">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ __('Attachments') }}</h6></div>
                <div class="card-body">
                    @forelse ($proposal_attachment as $file)
                        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2" data-attachment-row="{{ $file->id }}">
                            <a href="{{ check_file($file->file_path) ? get_file($file->file_path) : '#' }}" target="_blank" class="text-truncate me-2">
                                <i class="ti ti-paperclip me-1"></i>{{ $file->file_name }}
                                <span class="text-muted small">({{ $file->file_size }})</span>
                            </a>
                            @permission('proposal edit')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-attachment-delete="{{ route('proposal.attachment.destroy', $file->id) }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endpermission
                        </div>
                    @empty
                        <p class="text-muted small mb-3" id="modern-proposal-no-attachments">{{ __('No attachments yet.') }}</p>
                    @endforelse

                    @permission('proposal edit')
                        <div class="input-group input-group-sm">
                            <input type="file" class="form-control" id="modern-proposal-attachment-input">
                            <button type="button" class="btn btn-outline-primary" id="modern-proposal-attachment-upload">
                                <i class="ti ti-upload me-1"></i>{{ __('Upload') }}
                            </button>
                        </div>
                    @endpermission
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('modern-proposal-copy-link').addEventListener('click', function (e) {
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
        var uploadBtn = document.getElementById('modern-proposal-attachment-upload');
        var fileInput = document.getElementById('modern-proposal-attachment-input');

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', function () {
                if (!fileInput.files.length) {
                    return;
                }

                var formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                uploadBtn.disabled = true;

                fetch(@json(route('proposal.file.upload', $proposal->id)), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.is_success) {
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
    })();
</script>
@endpush
