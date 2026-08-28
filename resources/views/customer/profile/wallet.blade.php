<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ __('Balance') }}</h6>
    <span class="h5 mb-0">{{ $currency }}{{ number_format((float) $data['balance'], 2) }}</span>
</div>
<p class="text-muted small">
    {{ __('Reconciled from the ledger below, which is the record of truth.') }}
</p>

@if (empty($data['transactions']))
    <p class="text-muted mb-0">{{ __('No wallet activity.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['transactions'] as $transaction)
                    <tr>
                        <td class="text-nowrap">{{ $transaction->created_at ? $transaction->created_at->format('d M Y') : '-' }}</td>
                        <td>{{ $transaction->description ?: '-' }}</td>
                        <td><small class="text-muted">{{ $transaction->reference ?: '-' }}</small></td>
                        <td class="text-end {{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->type === 'credit' ? '+' : '−' }}{{ $currency }}{{ number_format((float) $transaction->amount, 2) }}
                        </td>
                        <td class="text-end">{{ $currency }}{{ number_format((float) $transaction->balance_after, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
