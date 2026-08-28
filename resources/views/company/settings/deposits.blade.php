{{-- Deposit rules and notification switches.

     The rules are evaluated once when an appointment is created, in the order
     shown, and the first match wins. --}}
@php
    $statusOptions = \App\Models\CustomStatus::where('created_by', creatorId())
        ->where('business_id', getActiveBusiness())
        ->orderBy('id')
        ->pluck('title', 'id');

    $gatewayReady = app(\App\Services\OnlinePaymentGate::class)->isOnlinePaymentReady();

    $value = function ($key, $fallback = '') use ($settings) {
        return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $fallback;
    };
@endphp

<div class="card" id="deposit-settings-sidenav">
    <div class="card-header p-3">
        <h5>{{ __('Appointment Deposits') }}</h5>
        <small class="text-secondary font-weight-bold">
            {{ __('Take money up front to secure a booking, automatically by rule or manually by staff.') }}
        </small>
    </div>

    {{ Form::open(['url' => route('company.deposit.settings.store'), 'method' => 'post']) }}
    <div class="card-body px-3">

        @if (!$gatewayReady)
            {{-- The gate that matters most: a deposit request is nothing but a
                 payment link, so without a gateway that link has no pay button. --}}
            <div class="alert alert-warning">
                <strong>{{ __('No online payment gateway is switched on.') }}</strong>
                {{ __('Deposit requests are refused until one is configured, because the payment link would have nothing for the customer to pay with. Taking a deposit at the counter still works.') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="deposit_auto_enabled" value="on"
                        id="deposit_auto_enabled" {{ $value('deposit_auto_enabled') === 'on' ? 'checked' : '' }}>
                    <label class="form-check-label" for="deposit_auto_enabled">
                        {{ __('Automatically request a deposit when a rule matches') }}
                    </label>
                </div>
                <small class="text-muted">
                    {{ __('Staff can always request a deposit manually, whether this is on or off.') }}
                </small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('Default deposit percentage') }}</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0.01" max="100" name="deposit_default_percentage"
                        class="form-control" value="{{ $value('deposit_default_percentage', '15') }}" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('Status after payment') }}</label>
                <select name="deposit_confirm_status" class="form-control">
                    <option value="">{{ __('Confirmed (default)') }}</option>
                    @foreach ($statusOptions as $id => $title)
                        <option value="{{ $id }}" {{ (string) $value('deposit_confirm_status') === (string) $id ? 'selected' : '' }}>
                            {{ $title }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    {{ __('Captured when the deposit is raised, so payment never has to work it out again.') }}
                </small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('History window (months)') }}</label>
                <input type="number" min="0" max="120" name="reliability_window_months" class="form-control"
                    value="{{ $value('reliability_window_months', '6') }}">
                <small class="text-muted">
                    {{ __('Used by the reliability badge. 0 counts a customer\'s whole history.') }}
                </small>
            </div>
        </div>

        <hr>
        <h6 class="mb-1">{{ __('Rules') }}</h6>
        <p class="text-muted small">
            {{ __('Checked in this order when an appointment is created. The first match wins.') }}
        </p>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="alert alert-light border py-2 mb-2">
                    <strong>1.</strong>
                    {{ __('Customer manually flagged high risk — always wins. Set this on a customer\'s profile.') }}
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label"><strong>2.</strong> {{ __('Service price at or above') }}</label>
                <input type="number" step="0.01" min="0" name="deposit_service_price_threshold"
                    class="form-control" value="{{ $value('deposit_service_price_threshold', '0') }}">
                <small class="text-muted">{{ __('0 disables this rule.') }}</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>3.</strong> {{ __('Cancellations reaching') }}</label>
                <input type="number" min="0" name="deposit_min_cancellations" class="form-control"
                    value="{{ $value('deposit_min_cancellations', '0') }}">
                <small class="text-muted">{{ __('0 disables this rule.') }}</small>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('…within the last (months)') }}</label>
                <input type="number" min="1" max="120" name="deposit_min_cancellations_months" class="form-control"
                    value="{{ $value('deposit_min_cancellations_months', '6') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>4.</strong> {{ __('No-shows reaching') }}</label>
                <input type="number" min="0" name="deposit_min_noshows" class="form-control"
                    value="{{ $value('deposit_min_noshows', '0') }}">
                <small class="text-muted">{{ __('0 disables this rule.') }}</small>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('…within the last (months)') }}</label>
                <input type="number" min="1" max="120" name="deposit_min_noshows_months" class="form-control"
                    value="{{ $value('deposit_min_noshows_months', '6') }}">
            </div>
        </div>

        <hr>
        <h6 class="mb-1">{{ __('Forfeiture') }}</h6>
        <p class="text-muted small">
            {{ __('A paid deposit is kept automatically when a booking is marked No Show. A cancellation only forfeits if it lands inside the window below.') }}
        </p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('Cancellation penalty window (hours before start)') }}</label>
                <input type="number" min="0" max="720" name="deposit_forfeit_window_hours" class="form-control"
                    value="{{ $value('deposit_forfeit_window_hours', '24') }}">
                <small class="text-muted">
                    {{ __('0 means a cancellation never forfeits — only a no-show does.') }}
                </small>
            </div>
        </div>

        <hr>
        <h6 class="mb-1">{{ __('Notifications') }}</h6>
        <p class="text-muted small">
            {{ __('Which template a customer receives is chosen by whether the rules engine or a staff member raised the deposit.') }}
        </p>

        <div class="row">
            @foreach (\App\Services\CustomerNotifier::EVENTS as $event => $definition)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-2">
                        <div class="fw-semibold mb-2">{{ __($definition['label']) }}</div>
                        @foreach (['sms' => __('SMS'), 'email' => __('Email')] as $channel => $channelLabel)
                            @php
                                // Key derived from the same call the sender uses to
                                // read it, so writer and reader cannot drift apart.
                                $key = \App\Services\CustomerNotifier::settingsKey($event, $channel);
                            @endphp
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="{{ $key }}" value="on"
                                    id="{{ $key }}"
                                    {{ ($settings[$key] ?? 'on') === 'on' ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $key }}">{{ $channelLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card-footer text-end">
        <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>
