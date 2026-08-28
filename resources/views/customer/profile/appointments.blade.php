@if (empty($data))
    <p class="text-muted mb-0">{{ __('No appointments yet.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Staff') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Deposit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $appointment)
                    <tr>
                        <td>{{ $appointment->date }}</td>
                        <td>{{ $appointment->time }}</td>
                        <td>{{ $appointment->ServiceData->name ?? '-' }}</td>
                        <td>{{ $appointment->StaffData->name ?? '-' }}</td>
                        <td>
                            <span class="badge"
                                style="background:#{{ ltrim($appointment->StatusData->status_color ?? '6c757d', '#') }}">
                                {{ $appointment->StatusData->title ?? __('Pending') }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($appointment->deposit_status && $appointment->deposit_status !== 'none')
                                <span class="badge bg-light text-dark">
                                    {{ __(ucfirst($appointment->deposit_status)) }}
                                    · {{ $currency }}{{ number_format((float) $appointment->deposit_amount, 2) }}
                                </span>
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
