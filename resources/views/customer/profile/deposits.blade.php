{{-- Outstanding deposits with a resend action, then the full deposit event log. --}}
<h6 class="mb-2">{{ __('Deposits') }}</h6>

@if (empty($data['bookings']))
    <p class="text-muted">{{ __('No deposits have been raised for this customer.') }}</p>
@else
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Booking') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['bookings'] as $booking)
                    @php $appointment = $booking['appointment']; @endphp
                    <tr>
                        <td>
                            <div>{{ $appointment->ServiceData->name ?? __('Booking') }}</div>
                            <small class="text-muted">{{ $appointment->common_number }}</small>
                        </td>
                        <td>{{ $appointment->date }}<br><small class="text-muted">{{ $appointment->time }}</small></td>
                        <td>
                            @php
                                $badge = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'forfeited' => 'dark',
                                    'refunded' => 'info',
                                ][$appointment->deposit_status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ __(ucfirst($appointment->deposit_status)) }}</span>
                            @if ($appointment->deposit_status === 'paid' && $appointment->deposit_method)
                                <br><small class="text-muted">{{ __(ucfirst(str_replace('_', ' ', $appointment->deposit_method))) }}</small>
                            @endif
                        </td>
                        <td class="text-end">{{ $currency }}{{ number_format((float) $appointment->deposit_amount, 2) }}</td>
                        <td class="text-end">
                            @if ($booking['can_resend'])
                                @permission('deposit manage')
                                    {{-- Creates no new deposit record and changes no deposit
                                         column: the link is rebuilt from the invoice each
                                         time, so a stale URL is impossible. --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-deposit-resend="{{ route('deposit.resend', $appointment->id) }}">
                                        {{ __('Resend link') }}
                                    </button>
                                    <span data-resend-result="{{ route('deposit.resend', $appointment->id) }}"></span>
                                @endpermission
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

<h6 class="mb-2">{{ __('Event log') }}</h6>

@if (empty($data['logs']))
    <p class="text-muted mb-0">{{ __('Nothing logged yet.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('By') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Detail') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['logs'] as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}</td>
                        <td>{{ $log->label() }}</td>
                        {{-- A null staff id means the automatic rule engine raised it. --}}
                        <td>{{ $log->staff->name ?? __('Automatic') }}</td>
                        <td class="text-end">
                            {{ $log->amount !== null ? $currency . number_format((float) $log->amount, 2) : '—' }}
                        </td>
                        <td><small class="text-muted">{{ $log->reason }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
