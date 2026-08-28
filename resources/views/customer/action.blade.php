<div class="header-btn-wrp d-flex gap-2">
@permission('customer manage')
    {{-- A full page rather than a modal: the profile is a tabbed screen with
         appointments, invoices, deposits and the message log on it. --}}
    <div class="action-btn me-1">
        <a href="{{ route('customer.profile', $customer->id) }}"
            class="btn btn-sm bg-primary d-inline align-items-center" data-bs-toggle="tooltip"
            title="{{ __('View Profile') }}"><span class="text-white"> <i class="ti ti-eye"></i></span></a>
    </div>
@endpermission
@permission('customer edit')
    <div class="action-btn me-1">
        <a data-size="lg" data-title="{{ __('Edit Customer') }}" data-ajax-popup="true"
            data-url="{{ route('customer.edit', $customer->id) }}"
            class="btn btn-sm bg-info  d-inline align-items-center" data-toggle="tooltip" title="{{ __('Edit') }}"><span
                class="text-white"> <i class="ti ti-pencil"></i></span></a>
    </div>
@endpermission
@permission('customer delete')
    <div class="action-btn">
        <form method="POST" action="{{ route('customer.destroy', $customer->id) }}"
            id="user-form-{{ $customer->id }}">
            @csrf
            @method('DELETE')
            <input name="_method" type="hidden" value="DELETE">
            <button type="button" class="btn btn-sm  bg-danger d-inline align-items-center show_confirm"
                data-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                <span class="text-white"> <i class="ti ti-trash"></i></span>
            </button>
        </form>
    </div>
@endpermission
</div>
