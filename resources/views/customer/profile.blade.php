@extends('layouts.main')

@php
    $user = $customer->customer;
    $summary = $panels['summary']['data'] ?? [];
    $reliability = $summary['reliability'] ?? ['signal' => 'green', 'label' => __('Good'), 'no_shows' => 0, 'cancelled_run' => 0];
    $signalClass = ['green' => 'success', 'orange' => 'warning', 'red' => 'danger'][$reliability['signal']] ?? 'secondary';

    // Disabled unless the contact detail can actually carry the channel —
    // see Customer::isValidAustralianMobile()/isValidEmail().
    $smsToggleDisabled = !\App\Models\Customer::isValidAustralianMobile($user->mobile_no ?? null);
    $emailToggleDisabled = !\App\Models\Customer::isValidEmail($user->email ?? null);

    // The layout splits page-breadcrumb on commas, so a name like "Smith, John"
    // would otherwise render as two separate crumbs.
    $crumbName = str_replace(',', ' ', $customer->name);
@endphp

@section('page-title')
    {{ $customer->name }}
@endsection
@section('page-breadcrumb')
    {{ __('Customers') }},{{ $crumbName }}
@endsection

@section('content')
<div class="row">

    {{-- Identity card --}}
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $user && $user->avatar ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}"
                    alt="{{ $customer->name }}" class="rounded-circle mb-3" width="88" height="88">
                <h5 class="mb-1">{{ $customer->name }}</h5>

                {{-- The reliability signal. Feeds the deposit rules engine, so it
                     is shown prominently rather than buried in a tab. --}}
                <span class="badge bg-{{ $signalClass }} mb-3">
                    <i class="ti ti-shield-check me-1"></i>{{ $reliability['label'] }}
                </span>

                @if ($customer->is_high_risk)
                    <div class="alert alert-danger py-2 small">
                        {{ __('Flagged high risk — every new booking requires a deposit.') }}
                    </div>
                @endif

                @if ($customer->is_walkin)
                    <div class="alert alert-secondary py-2 small">
                        {{ __('Walk-in customer — no contact details required, no SMS or email will be sent.') }}
                    </div>
                @endif

                <ul class="list-unstyled text-start mt-3 mb-0">
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Mobile') }}</span>
                        <span>{{ $user->mobile_no ?? '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Email') }}</span>
                        <span>{{ $user->email ?? '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Gender') }}</span>
                        <span>{{ $customer->gender ?: '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Date of birth') }}</span>
                        <span>{{ $customer->dob ?: '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">{{ __('Notifications') }}</span>
                        <span>{{ __(ucfirst($customer->notification_preference ?: 'both')) }}</span>
                    </li>
                </ul>

                @permission('customer edit')
                    <form method="POST" action="{{ route('customer.toggle-risk', $customer->id) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-sm w-100 btn-outline-{{ $customer->is_high_risk ? 'secondary' : 'danger' }}">
                            {{ $customer->is_high_risk ? __('Remove high-risk flag') : __('Flag as high risk') }}
                        </button>
                    </form>
                @endpermission
            </div>
        </div>

        {{-- Communication Group: SMS/email opt-in, gated by valid contact details --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Communication Group') }}</h6></div>
            <div class="card-body">
                @if ($customer->is_walkin)
                    <p class="text-muted small mb-0">{{ __('Walk-in customer — communication is off regardless of these preferences.') }}</p>
                @elseif (!Auth::user()->isAbleTo('customer edit'))
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" disabled {{ $customer->communication_sms && !$smsToggleDisabled ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('SMS notifications') }}</label>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" disabled {{ $customer->communication_email && !$emailToggleDisabled ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Email notifications') }}</label>
                    </div>
                @else
                    <form method="POST" action="{{ route('customer.communication.update', $customer->id) }}">
                        @csrf
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" id="communication_sms" name="communication_sms"
                                value="1"
                                {{ $customer->communication_sms && !$smsToggleDisabled ? 'checked' : '' }}
                                {{ $smsToggleDisabled ? 'disabled' : '' }}>
                            <label class="form-check-label" for="communication_sms">{{ __('SMS notifications') }}</label>
                            @if ($smsToggleDisabled)
                                <div class="form-text text-danger mb-0">{{ __('Requires a valid Australian mobile number (04xxxxxxxx or +614xxxxxxxx).') }}</div>
                            @endif
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="communication_email" name="communication_email"
                                value="1"
                                {{ $customer->communication_email && !$emailToggleDisabled ? 'checked' : '' }}
                                {{ $emailToggleDisabled ? 'disabled' : '' }}>
                            <label class="form-check-label" for="communication_email">{{ __('Email notifications') }}</label>
                            @if ($emailToggleDisabled)
                                <div class="form-text text-danger mb-0">{{ __('Requires a valid email address on file.') }}</div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">{{ __('Save preferences') }}</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Lifetime totals --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Totals') }}</h6></div>
            <div class="card-body">
                @if ($panels['summary']['failed'])
                    @include('customer.profile.failed')
                @else
                    <div class="row text-center g-2">
                        @foreach ([
                            __('Lifetime sales') => $currency . number_format($summary['lifetime_sales'] ?? 0, 2),
                            __('Outstanding') => $currency . number_format($summary['outstanding'] ?? 0, 2),
                            ...($creditNotesEnabled ? [__('Store credit') => $currency . number_format($summary['store_credit'] ?? 0, 2)] : []),
                            __('Appointments') => $summary['total_appointments'] ?? 0,
                            __('Completed') => $summary['completed'] ?? 0,
                            __('Cancelled') => $summary['cancelled'] ?? 0,
                            __('No-shows') => $summary['no_shows'] ?? 0,
                        ] as $label => $value)
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">{{ $value }}</div>
                                    <small class="text-muted">{{ $label }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header pb-0">
                @php
                    $profileTabs = ['appointments' => __('Appointments')];
                    if (!empty($jobCardEnabled)) {
                        $profileTabs['job_cards'] = __('Job Cards');
                    }
                    $profileTabs += [
                        'invoices' => __('Invoices'),
                        'deposits' => __('Deposits'),
                        'wallet' => __('Wallet'),
                        'loyalty' => __('Loyalty'),
                        'notes' => __('Notes'),
                        'sms' => __('SMS'),
                    ];
                    if (!empty($creditNotesEnabled)) {
                        $profileTabs['credit_notes'] = __('Store Credit');
                    }
                @endphp
                <ul class="nav nav-tabs" role="tablist">
                    @foreach ($profileTabs as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#tab-{{ $key }}" role="tab">
                                {{ $label }}
                                @if ($panels[$key]['failed'])
                                    <i class="ti ti-alert-triangle text-warning ms-1"></i>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    @foreach (array_keys($profileTabs) as $key)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                            @if ($panels[$key]['failed'])
                                @include('customer.profile.failed')
                            @else
                                @include('customer.profile.' . $key, [
                                    'data' => $panels[$key]['data'],
                                    'customer' => $customer,
                                    'currency' => $currency,
                                ])
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Resend reports the *real* send outcome: the service sends synchronously and
    // reads the message log back, so a failure here means the SMS did not leave.
    document.querySelectorAll('[data-deposit-resend]').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = button.getAttribute('data-deposit-resend');
            var note = document.querySelector('[data-resend-result="' + target + '"]');

            button.disabled = true;
            button.textContent = @json(__('Sending…'));

            fetch(target, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(function (response) { return response.json(); })
            .then(function (body) {
                note.textContent = body.message;
                note.className = body.success ? 'small text-success d-block mt-1' : 'small text-danger d-block mt-1';
            })
            .catch(function () {
                note.textContent = @json(__('The request failed. Try again.'));
                note.className = 'small text-danger d-block mt-1';
            })
            .finally(function () {
                button.disabled = false;
                button.textContent = @json(__('Resend link'));
            });
        });
    });
</script>
@endpush
