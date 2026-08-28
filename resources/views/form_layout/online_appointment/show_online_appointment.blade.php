@php
    $onlineAppointment = explode(',', $service->online_appointments);
@endphp
@if ($business->layouts == 'Formlayout1')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout2')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="BACK" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout3')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Payment:') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="BACK" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout4')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout5')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout6')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout7')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout8')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout9')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif

@if ($business->layouts == 'Formlayout10')
    <div class="appointment-wrp">
        <div class="appointment-form payment-method-form">
            <div class="section-title">
                <h3>{{ __('Online Appointment') }}</h3>
            </div>
            <div class="row">
                @if (in_array('zoom meeting', $onlineAppointment))
                    @stack('zoom_meeting')
                @endif
                @if (in_array('google meet', $onlineAppointment))
                    @stack('google_meet')
                @endif
            </div>
        </div>
        <div class="step-btns">
            <button type="button" name="back" class="back btn btn-transparent">
                {{ __('Back') }}
            </button>
            <button type="button" name="next" class="next btn">
                {{ __('Next') }}
            </button>
        </div>
    </div>
@endif
