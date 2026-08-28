@php
    $onlineAppointment = explode(',', $service->online_appointments);
@endphp
<div class="appointment-wrp">
    <div class="appointment-form payment-method-form">
        <div class="section-title">
            <h3 class="h5">{{ __('Online Meeting :') }}</h3>
        </div>
        <div class="row row-gaps">
            @if (in_array('zoom meeting', $onlineAppointment))
                @stack('zoom_meeting')
            @endif
            @if (in_array('google meet', $onlineAppointment))
                @stack('google_meet')
            @endif
        </div>
    </div>
    <div class="step-btns">
        <button type="button" name="BACK" class="action-button back btn btn-transparent">
            {{ __('Back') }}
        </button>

        <button type="button" name="next" class="next action-button btn">
            {{ __('Next') }}
        </button>
    </div>
</div>
