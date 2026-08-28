{{-- Every message to and from this customer's mobile, inbound replies included. --}}
@if (empty($data))
    <p class="text-muted mb-0">{{ __('No messages yet.') }}</p>
@else
    <ul class="list-unstyled mb-0">
        @foreach ($data as $message)
            <li class="border-bottom py-2 d-flex justify-content-between gap-3">
                <div>
                    <div>{{ $message->message }}</div>
                    <small class="text-muted">
                        {{ $message->direction === 'in' ? __('Received') : __('Sent') }}
                        · {{ $message->created_at ? $message->created_at->format('d M Y H:i') : '' }}
                        @if ($message->event)
                            · {{ $message->event }}
                        @endif
                    </small>
                    @if ($message->status === 'failed' && $message->error)
                        <small class="text-danger d-block">{{ $message->error }}</small>
                    @endif
                </div>
                <div class="text-end text-nowrap">
                    @php
                        $badge = [
                            'sent' => 'success',
                            'queued' => 'secondary',
                            'failed' => 'danger',
                            'received' => 'info',
                        ][$message->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badge }}">{{ __(ucfirst($message->status)) }}</span>
                </div>
            </li>
        @endforeach
    </ul>
@endif
