@permission('customer edit')
    <form method="POST" action="{{ route('customer.note.store', $customer->id) }}" class="mb-4">
        @csrf
        <div class="input-group">
            <input type="text" name="note" class="form-control" required
                placeholder="{{ __('Add a note about this customer…') }}">
            <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
        </div>
    </form>
@endpermission

@if (!empty($customer->description))
    <div class="alert alert-light border mb-3">
        <small class="text-muted d-block">{{ __('Profile note') }}</small>
        {{ $customer->description }}
    </div>
@endif

@if (empty($data))
    <p class="text-muted mb-0">{{ __('No notes yet.') }}</p>
@else
    <ul class="list-unstyled mb-0">
        @foreach ($data as $note)
            <li class="border-bottom py-2">
                <div>{{ $note->note }}</div>
                <small class="text-muted">
                    {{ $note->staff->name ?? __('Staff') }}
                    · {{ $note->created_at ? $note->created_at->format('d M Y H:i') : '' }}
                </small>
            </li>
        @endforeach
    </ul>
@endif
