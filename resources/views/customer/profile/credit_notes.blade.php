<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ __('Available store credit') }}</h6>
    <span class="h5 mb-0">{{ $currency }}{{ number_format((float) $data['available_total'], 2) }}</span>
</div>
<p class="text-muted small">
    {{ __('Issued as a credit note against a past invoice — redeemable as a payment method on any future invoice for this customer.') }}
</p>

@if (empty($data['notes']) || $data['notes']->isEmpty())
    <p class="text-muted mb-0">{{ __('No credit notes issued yet.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Source Invoice') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Remaining') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['notes'] as $note)
                    @php
                        $sourceInvoice = \Workdo\Invoice\Entities\Invoice::find($note->invoice);
                        $statusLabel = \Workdo\Account\Entities\CustomerCreditNotes::$statues[$note->status] ?? __('Pending');
                        $statusClass = [0 => 'secondary', 1 => 'warning', 2 => 'success'][$note->status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="text-nowrap">{{ $note->date ? \Illuminate\Support\Carbon::parse($note->date)->format('d M Y') : '-' }}</td>
                        <td>
                            @if ($sourceInvoice)
                                @permission('invoice show')
                                    <a href="{{ route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($sourceInvoice->id)) }}">
                                        {{ \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($sourceInvoice->invoice_id, $sourceInvoice->created_by, $sourceInvoice->workspace ?? null) }}
                                    </a>
                                @else
                                    {{ \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($sourceInvoice->invoice_id, $sourceInvoice->created_by, $sourceInvoice->workspace ?? null) }}
                                @endpermission
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $note->description ?: '-' }}</td>
                        <td><span class="badge bg-{{ $statusClass }}">{{ __($statusLabel) }}</span></td>
                        <td class="text-end">{{ $currency }}{{ number_format((float) $note->amount, 2) }}</td>
                        <td class="text-end fw-semibold">{{ $currency }}{{ number_format((float) $note->remaining_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
