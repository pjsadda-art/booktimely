@if (empty($data))
    <p class="text-muted mb-0">{{ __('No invoices yet.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Invoice') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Issued') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                    <th class="text-end">{{ __('Outstanding') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number ?: '#' . $invoice->id }}</td>
                        <td>{{ __(ucfirst($invoice->invoice_type)) }}</td>
                        <td>{{ $invoice->issue_date }}</td>
                        <td>
                            <span class="badge bg-{{ $invoice->isPaid() ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'secondary') }}">
                                {{ __(ucfirst($invoice->status)) }}
                            </span>
                        </td>
                        <td class="text-end">{{ $currency }}{{ number_format((float) $invoice->total, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($invoice->outstanding(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
