{{-- Take a deposit at reception.

     Deliberately available whether or not an online gateway is configured: this
     is the path a business without a gateway depends on. --}}
@php
    $currency = company_setting('defult_currancy_symbol', null, $appointment->business_id) ?: '$';
    $suggested = $appointment->deposit_amount ?: null;
@endphp

@if (!$lock['allowed'])
    <div class="modal-body">
        <div class="alert alert-warning mb-0">
            <i class="ti ti-lock me-1"></i>{{ $lock['reason'] }}
        </div>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@else
    {{ Form::open(['route' => ['deposit.pay-on-spot', $appointment->id], 'id' => 'deposit-payment-form']) }}
    <div class="modal-body">
        <div class="alert alert-light-primary border-0 mb-3 d-flex justify-content-between">
            <span>{{ __('Booking total') }}</span>
            <strong>{{ $currency }}{{ number_format($total, 2) }}</strong>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                {{ Form::label('amount', __('Amount collected'), ['class' => 'form-label']) }}
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                    value="{{ $suggested }}" required placeholder="0.00">
            </div>

            <div class="col-md-6 mb-3">
                {{ Form::label('method', __('Method'), ['class' => 'form-label']) }}
                <select name="method" class="form-control" required>
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 mb-3">
                {{ Form::label('reference', __('Reference (optional)'), ['class' => 'form-label']) }}
                <input type="text" name="reference" class="form-control"
                    placeholder="{{ __('Receipt or transfer reference') }}">
            </div>

            <div class="col-md-12">
                {{ Form::label('notes', __('Notes (optional)'), ['class' => 'form-label']) }}
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <p class="text-muted mt-3 mb-0 small">
            <i class="ti ti-info-circle me-1"></i>{{ __('This records a real payment against the deposit invoice, so it appears in financial reporting exactly as an online payment does.') }}
        </p>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Record payment'), ['class' => 'btn m-0 btn-primary']) }}
    </div>
    {{ Form::close() }}
@endif
