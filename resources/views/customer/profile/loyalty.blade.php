@if (empty($data['enabled']))
    <p class="text-muted mb-0">{{ __('The loyalty programme is switched off for this business.') }}</p>
@elseif ($data['mode'] === 'points')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('Points balance') }}</h6>
        <span class="h5 mb-0">{{ number_format($data['points']) }}</span>
    </div>

    @if (empty($data['transactions']))
        <p class="text-muted mb-0">{{ __('No points activity.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Points') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['transactions'] as $transaction)
                        <tr>
                            <td class="text-nowrap">{{ $transaction->created_at ? $transaction->created_at->format('d M Y') : '-' }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td class="text-end {{ $transaction->type === 'redeem' ? 'text-danger' : 'text-success' }}">
                                {{ $transaction->type === 'redeem' ? '−' : '+' }}{{ number_format(abs((int) $transaction->points)) }}
                            </td>
                            <td class="text-end">{{ number_format((int) $transaction->balance_after) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@else
    {{-- Service mode: a per-customer, per-service visit counter. --}}
    @if (empty($data['visits']))
        <p class="text-muted mb-0">{{ __('No qualifying visits yet.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Visits') }}</th>
                        <th>{{ __('Progress') }}</th>
                        <th class="text-end">{{ __('Reward') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['visits'] as $visit)
                        @php
                            $required = max(1, (int) $data['required']);
                            $percent = min(100, round(((int) $visit->visits / $required) * 100));
                        @endphp
                        <tr>
                            <td>{{ $visit->service->name ?? __('Service') . ' #' . $visit->service_id }}</td>
                            <td>{{ $visit->visits }} / {{ $required }}</td>
                            <td style="min-width:140px;">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar" style="width: {{ $percent }}%"></div>
                                </div>
                            </td>
                            <td class="text-end">
                                @if ($visit->free_visit_available)
                                    <span class="badge bg-success">{{ __('Free visit available') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif
