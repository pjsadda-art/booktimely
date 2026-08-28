<div class="d-flex">
@permission('servicetax edit')
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm bg-info d-inline align-items-center"
            data-url="{{ route('servicetax.edit', $serviceTax->id) }}" class="dropdown-item" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Edit Service Tax') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}">
            <span class="text-white"> <i class="ti ti-pencil"></i></span></a>
    </div>
@endpermission
@permission('servicetax delete')
    <div class="action-btn">
        <form method="GET" action="{{ route('servicetax.delete', $serviceTax->id) }}" id="user-form-{{ $serviceTax->id }}">
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
