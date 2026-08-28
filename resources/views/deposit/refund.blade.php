{{-- Record that a paid deposit was returned.

     The actual refund happens in the gateway or the till; this records the
     decision and the external reference that proves it. --}}
{{ Form::open(['route' => ['deposit.refund', $appointment->id]]) }}
<div class="modal-body">
    <div class="alert alert-info">
        {{ __('This does not move any money. Process the refund in your payment gateway or till first, then record its reference here.') }}
    </div>

    <div class="form-group mb-3">
        {{ Form::label('reference', __('Refund reference'), ['class' => 'form-label']) }}
        <input type="text" name="reference" class="form-control" required
            placeholder="{{ __('Gateway refund id or receipt number') }}">
    </div>

    <div class="form-group">
        {{ Form::label('reason', __('Reason (optional)'), ['class' => 'form-label']) }}
        <textarea name="reason" class="form-control" rows="2"></textarea>
    </div>
</div>
<div class="modal-footer gap-3 pt-3 p-0">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Mark as refunded'), ['class' => 'btn m-0 btn-primary']) }}
</div>
{{ Form::close() }}
