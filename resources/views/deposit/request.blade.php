{{-- Raise a deposit against a booking group. --}}
@php
    $currency = company_setting('defult_currancy_symbol', null, $appointment->business_id) ?: '$';
@endphp

@if (!$lock['allowed'])
    {{-- The booking is already settled in full, so every deposit action is refused. --}}
    <div class="modal-body">
        <div class="alert alert-warning mb-0">
            <i class="ti ti-lock me-1"></i>{{ $lock['reason'] }}
        </div>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@elseif (!$gatewayReady)
    {{-- A deposit request is nothing but a payment link. Without a gateway that
         link has no pay button, so raising one would strand the customer. --}}
    <div class="modal-body">
        <div class="alert alert-danger mb-0">
            <h6 class="alert-heading">{{ __('No online payment gateway is available') }}</h6>
            <p class="mb-2">{{ __('A deposit request sends the customer a payment link. With no gateway switched on, that link would have nothing for them to pay with.') }}</p>
            <p class="mb-0">{{ __('You can still take a deposit at the counter using Take Payment.') }}</p>
        </div>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@else
    {{ Form::open(['route' => ['deposit.store', $appointment->id], 'id' => 'deposit-request-form']) }}
    <div class="modal-body">
        <div class="alert alert-light-primary border-0 mb-3">
            <div class="d-flex justify-content-between">
                <span>{{ __('Booking total') }}</span>
                <strong>{{ $currency }}{{ number_format($total, 2) }}</strong>
            </div>
            <div class="d-flex justify-content-between text-muted">
                <span>{{ __('Services in this booking') }}</span>
                <span>{{ count($group) }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                {{ Form::label('preset', __('Deposit amount'), ['class' => 'form-label']) }}
                {{-- 15% and 20% are the two figures staff reach for almost every
                     time, so they are one click rather than a typed number. --}}
                @php
                    // The business's configured default wins where it matches a
                    // preset; anything else starts on 15% but with the configured
                    // figure already in the field.
                    $initialPreset = in_array((float) $defaultPercentage, [15.0, 20.0], true)
                        ? (string) (int) $defaultPercentage
                        : '15';
                @endphp
                <div class="btn-group w-100" role="group" id="deposit-presets"
                    data-initial="{{ $initialPreset }}" data-default-percentage="{{ $defaultPercentage }}">
                    <button type="button" class="btn btn-outline-primary {{ $initialPreset === '15' ? 'active' : '' }}"
                        data-preset="15">15%</button>
                    <button type="button" class="btn btn-outline-primary {{ $initialPreset === '20' ? 'active' : '' }}"
                        data-preset="20">20%</button>
                    <button type="button" class="btn btn-outline-primary" data-preset="custom">{{ __('Custom') }}</button>
                </div>
                {{-- The real submitted value. The buttons only drive it. --}}
                <input type="hidden" name="mode" id="deposit-mode" value="percentage">
            </div>

            <div class="col-md-12 mb-3" id="deposit-percentage-wrap">
                {{ Form::label('percentage', __('Percentage (%)'), ['class' => 'form-label']) }}
                <input type="number" name="percentage" id="deposit-percentage" class="form-control"
                    step="0.01" min="0.01" max="100" value="15">
                <small class="text-muted">
                    {{ __('Deposit') }}: <span id="deposit-preview">{{ $currency }}{{ number_format($total * 0.15, 2) }}</span>
                </small>
            </div>

            <div class="col-md-12 mb-3 d-none" id="deposit-fixed-wrap">
                {{ Form::label('amount', __('Fixed amount'), ['class' => 'form-label']) }}
                <input type="number" name="amount" id="deposit-amount" class="form-control"
                    step="0.01" min="0.01" max="{{ $total }}" placeholder="0.00">
                <small class="text-muted">
                    {{ __('Cannot exceed the booking total of :total.', ['total' => $currency . number_format($total, 2)]) }}
                </small>
            </div>

            <div class="col-md-12">
                {{ Form::label('message', __('Note to customer (optional)'), ['class' => 'form-label']) }}
                <textarea name="message" class="form-control" rows="2"
                    placeholder="{{ __('Shown in the deposit request message.') }}"></textarea>
            </div>
        </div>

        <p class="text-muted mt-3 mb-0 small">
            <i class="ti ti-info-circle me-1"></i>{{ __('The booking moves to Deposit Pending and the customer is sent a payment link by SMS and email.') }}
        </p>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Send deposit request'), ['class' => 'btn m-0 btn-primary']) }}
    </div>
    {{ Form::close() }}

    <script>
        (function () {
            var total = {{ (float) $total }};
            var symbol = @json($currency);
            var mode = document.getElementById('deposit-mode');
            var percentage = document.getElementById('deposit-percentage');
            var amount = document.getElementById('deposit-amount');
            var preview = document.getElementById('deposit-preview');
            var percentageWrap = document.getElementById('deposit-percentage-wrap');
            var fixedWrap = document.getElementById('deposit-fixed-wrap');

            function showAmount() {
                var value = parseFloat(percentage.value);
                if (isNaN(value)) { value = 0; }
                preview.textContent = symbol + (total * value / 100).toFixed(2);
            }

            function select(preset) {
                var isCustom = preset === 'custom';

                mode.value = isCustom ? 'fixed' : 'percentage';
                percentageWrap.classList.toggle('d-none', isCustom);
                fixedWrap.classList.toggle('d-none', !isCustom);

                // Clear the field that is not in play, so a stale value from the
                // other mode cannot be submitted alongside the one in use.
                if (isCustom) {
                    percentage.value = '';
                } else {
                    amount.value = '';
                    percentage.value = preset;
                    showAmount();
                }
            }

            document.querySelectorAll('#deposit-presets [data-preset]').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.querySelectorAll('#deposit-presets [data-preset]').forEach(function (other) {
                        other.classList.remove('active');
                    });
                    button.classList.add('active');
                    select(button.getAttribute('data-preset'));
                });
            });

            // Typing a percentage by hand still updates the preview, so "15%"
            // can be nudged to 17.5 without leaving the preset row.
            percentage.addEventListener('input', showAmount);

            var presets = document.getElementById('deposit-presets');
            select(presets.getAttribute('data-initial'));

            // A configured default that is neither 15 nor 20 still applies — the
            // row just shows 15% as the highlighted preset.
            var configured = parseFloat(presets.getAttribute('data-default-percentage'));
            if (!isNaN(configured) && configured !== 15 && configured !== 20) {
                percentage.value = configured;
                showAmount();
            }
        })();
    </script>
@endif
