<div class="d-flex action-btn-wrapper">
<div class="action-btn">
    <a class="btn btn-sm bg-primary  align-items-center"
        data-url="{{ route('bank-transfer-request.edit', $bank_transfer_payment->id) }}" data-ajax-popup="true"
        data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('Request Action') }}"
        data-bs-original-title="{{ __('Action') }}">
        <i class="ti ti-caret-right text-white"></i>
    </a>
</div>

<div class="action-btn">
    {{ Form::open(['route' => ['bank-transfer-request.destroy', $bank_transfer_payment->id], 'class' => 'm-0']) }}
    @method('DELETE')
    <a class="btn btn-sm  bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title=""
        data-bs-original-title="Delete" aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
        data-confirm-yes="delete-form-{{ $bank_transfer_payment->id }}"><i
            class="ti ti-trash text-white text-white"></i></a>
    {{ Form::close() }}
</div>
</div>
