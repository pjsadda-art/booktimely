{{-- Keep the deposit after a no-show or a late cancellation.

     Moves no money by itself — the money is already collected. This records the
     decision and the reason for it. --}}
{{ Form::open(['route' => ['deposit.forfeit', $appointment->id]]) }}
<div class="modal-body">
    <div class="alert alert-warning">
        {{ __('The deposit is kept by the business. This cannot be undone from here — a forfeited deposit would have to be refunded manually.') }}
    </div>

    <div class="form-group">
        {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
        <textarea name="reason" class="form-control" rows="3" required
            placeholder="{{ __('e.g. Customer did not attend and gave no notice.') }}"></textarea>
    </div>
</div>
<div class="modal-footer gap-3 pt-3 p-0">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Forfeit deposit'), ['class' => 'btn m-0 btn-danger']) }}
</div>
{{ Form::close() }}
