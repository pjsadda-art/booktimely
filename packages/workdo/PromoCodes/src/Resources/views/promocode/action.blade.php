<div class="d-flex">
@permission('promocode manage')
    <div class="action-btn me-2">
        <a href="{{ route('promocode.show', $promocode->id) }}" class="btn btn-sm bg-warning d-inline align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
            <span class="text-white"><i class="ti ti-eye"></i></span>
        </a>
    </div>
@endpermission
@permission('promocode edit')
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm bg-info d-inline align-items-center"
            data-url="{{ route('promocode.edit', $promocode->id) }}" class="dropdown-item" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Edit Promo Code') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}">
            <span class="text-white"> <i class="ti ti-pencil"></i></span></a>
    </div>
@endpermission
@permission('promocode delete')
    <div class="action-btn">
        <form method="GET" action="{{ route('promocode.delete', $promocode->id) }}" id="user-form-{{ $promocode->id }}">
            @csrf
            @method('DELETE')
            <input name="_method" type="hidden" value="DELETE">
            <button type="button" class="btn btn-sm  bg-danger d-inline align-items-center show_confirm"
                data-bs-toggle="tooltip" title='{{ __('Delete')}}'>
                <span class="text-white"> <i class="ti ti-trash"></i></span>
            </button>
        </form>
    </div>
@endpermission
</div>
