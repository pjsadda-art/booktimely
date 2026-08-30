<div class="d-flex">
    @if (module_is_active('Tip'))
    @include('tip::tip.tip_action', ['object' => $Appointment])
    @endif

    @if (module_is_active('GoogleCalendar') && isset($company_settings['google_calendar_enable']) &&
    $company_settings['google_calendar_enable'] == 'on' && $Appointment->is_sync == 1)
    <div class="action-btn me-2" id="sendDataButton">
        <a href="#" class="btn btn-sm bg-primary-subtle d-inline align-items-center"
            data-url="{{ route('google.calendar.sync', $Appointment->id) }}" class="dropdown-item"
            data-ajax-popup="false" data-title="{{ __('Edit Appointment') }}" data-bs-toggle="tooltip" data-size="lg"
            data-bs-original-title="{{ __('Sync Google Calendar') }}">
            <span class="text-primary-emphasis">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                    fill="#FFFFFF">
                    <path
                        d="M160-160v-80h110l-16-14q-52-46-73-105t-21-119q0-111 66.5-197.5T400-790v84q-72 26-116 88.5T240-478q0 45 17 87.5t53 78.5l10 10v-98h80v240H160Zm400-10v-84q72-26 116-88.5T720-482q0-45-17-87.5T650-648l-10-10v98h-80v-240h240v80H690l16 14q49 49 71.5 106.5T800-482q0 111-66.5 197.5T560-170Z" />
                </svg>
            </span>
        </a>
    </div>
    @endif

    @if (module_is_active('OutlookCalendar') &&
    isset($company_settings['outlook_calendar_enable']) &&
    $company_settings['outlook_calendar_enable'] == 'on' &&
    $Appointment->outlook_is_sync == 1)
    <div class="action-btn me-2 o-calender me-2" id="sendDataButton">
        <a href="#" class="btn btn-sm d-inline  bg-secondary align-items-center"
            data-url="{{ route('outlook.calendar.sync', $Appointment->id) }}" class="dropdown-item"
            data-ajax-popup="false" data-title="{{ __('Edit Appointment') }}" data-bs-toggle="tooltip" data-size="lg"
            data-bs-original-title="{{ __('Sync Outlook Calendar') }}">
            <span class="text-white">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 32 32">
                    <path
                        d="M 15.851562 4 L 15.744141 4.0234375 L 3.9609375 6.6425781 L 3.9609375 25.357422 L 15.851562 28 L 17.960938 28 L 17.960938 25 L 28 25 L 28 7 L 17.960938 7 L 17.960938 4 L 15.851562 4 z M 15.960938 6.0253906 L 15.960938 25.976562 L 5.9609375 23.751953 L 5.9609375 8.2460938 L 15.960938 6.0253906 z M 17.960938 9 L 26 9 L 26 11 L 17.960938 11 L 17.960938 9 z M 10.544922 11 C 9.4649219 11 8.5983594 11.473016 7.9433594 12.416016 C 7.2883594 13.360016 6.9609375 14.596 6.9609375 16.125 C 6.9609375 17.572 7.2847344 18.747438 7.9277344 19.648438 C 8.5717344 20.550438 9.4057344 21 10.427734 21 C 11.476734 21 12.328422 20.533516 12.982422 19.603516 C 13.634422 18.672516 13.960938 17.449687 13.960938 15.929688 C 13.960938 14.450688 13.645578 13.259469 13.017578 12.355469 C 12.388578 11.452469 11.563922 11 10.544922 11 z M 10.492188 12.996094 C 10.947188 12.996094 11.305406 13.263781 11.566406 13.800781 C 11.829406 14.336781 11.960937 15.085828 11.960938 16.048828 C 11.960938 16.973828 11.825688 17.696797 11.554688 18.216797 C 11.284688 18.737797 10.918984 18.996094 10.458984 18.996094 C 10.011984 18.996094 9.651 18.728359 9.375 18.193359 C 9.099 17.656359 8.9609375 16.929766 8.9609375 16.009766 C 8.9609375 15.102766 9.099 14.373266 9.375 13.822266 C 9.651 13.272266 10.023187 12.996094 10.492188 12.996094 z M 17.960938 13 L 26 13 L 26 23 L 17.960938 23 L 17.960938 21 L 20 21 L 20 19 L 17.960938 19 L 17.960938 17 L 20 17 L 20 15 L 17.960938 15 L 17.960938 13 z M 22 15 L 22 17 L 24 17 L 24 15 L 22 15 z M 22 19 L 22 21 L 24 21 L 24 19 L 22 19 z">
                    </path>
                </svg>
            </span>
        </a>
    </div>
    @endif

    @if (module_is_active('EasyDepositPayments'))
    @include('easy-deposit-payments::deposit.payment')
    @endif

    @if (module_is_active('AdditionalServices'))
    @permission('additional quanitty edit')
    @if ($Appointment->additional_service_id)
    @include('additional-services::additional_service.appointment_action', [
    'appointment' => $Appointment,
    ])
    @endif
    @endpermission
    @endif

    {{-- Deposits. Which actions are offered follows deposit_status, so staff are
         never shown a button that the service would refuse. --}}
    @permission('deposit manage')
    @php $depositStatus = $Appointment->deposit_status ?: 'none'; @endphp

    @if ($depositStatus === 'none')
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm bg-warning d-inline align-items-center"
                data-url="{{ route('deposit.create', $Appointment->id) }}" data-ajax-popup="true"
                data-title="{{ __('Request Deposit') }}" data-bs-toggle="tooltip" data-size="md"
                data-bs-original-title="{{ __('Request Deposit') }}">
                <span class="text-white"><i class="ti ti-cash"></i></span>
            </a>
        </div>
    @endif

    @if (in_array($depositStatus, ['none', 'pending']))
        {{-- Deliberately available with no gateway configured: this is the path a
             business without one depends on. --}}
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm bg-success d-inline align-items-center"
                data-url="{{ route('deposit.payment-form', $Appointment->id) }}" data-ajax-popup="true"
                data-title="{{ __('Take Deposit Payment') }}" data-bs-toggle="tooltip" data-size="md"
                data-bs-original-title="{{ __('Take Payment') }}">
                <span class="text-white"><i class="ti ti-credit-card"></i></span>
            </a>
        </div>
    @endif

    @if (in_array($depositStatus, ['pending', 'paid']))
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm bg-dark d-inline align-items-center"
                data-url="{{ route('deposit.forfeit-form', $Appointment->id) }}" data-ajax-popup="true"
                data-title="{{ __('Forfeit Deposit') }}" data-bs-toggle="tooltip" data-size="md"
                data-bs-original-title="{{ __('Forfeit Deposit') }}">
                <span class="text-white"><i class="ti ti-ban"></i></span>
            </a>
        </div>
    @endif

    @if ($depositStatus === 'paid')
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm bg-secondary d-inline align-items-center"
                data-url="{{ route('deposit.refund-form', $Appointment->id) }}" data-ajax-popup="true"
                data-title="{{ __('Refund Deposit') }}" data-bs-toggle="tooltip" data-size="md"
                data-bs-original-title="{{ __('Refund Deposit') }}">
                <span class="text-white"><i class="ti ti-arrow-back-up"></i></span>
            </a>
        </div>
    @endif
    @endpermission

    @if (jobCardFeatureEnabled())
    @permission('appointment edit')
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm bg-primary d-inline align-items-center"
            data-url="{{ route('job-card.show', $Appointment->id) }}" data-ajax-popup="true"
            data-title="{{ __('Job Card') }}" data-bs-toggle="tooltip" data-size="md"
            data-bs-original-title="{{ __('Upload Job Card') }}">
            <span class="text-white"><i class="ti ti-file-upload"></i></span>
        </a>
    </div>
    @endpermission
    @endif

    @permission('appointment edit')
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm bg-info  d-inline align-items-center"
            data-url="{{ route('appointment.edit', $Appointment->id) }}" class="dropdown-item" data-ajax-popup="true"
            data-title="{{ __('Edit Appointment') }}" data-bs-toggle="tooltip" data-size="lg"
            data-bs-original-title="{{ __('Edit') }}">
            <span class="text-white"> <i class="ti ti-pencil"></i></span></a>
    </div>
    @endpermission

    @permission('appointment delete')
    <div class="action-btn">
        <form method="POST" action="{{ route('appointment.destroy', $Appointment->id) }}"
            id="user-form-{{ $Appointment->id }}">
            @csrf
            @method('DELETE')
            <input name="_method" type="hidden" value="DELETE">
            <button type="button" class="btn btn-sm bg-danger d-inline align-items-center show_confirm"
                data-bs-toggle="tooltip" title='Delete'>
                <span class="text-white"> <i class="ti ti-trash"></i></span>
            </button>
        </form>
    </div>
    @endpermission
    @php
    $amount = $appointment->payment->amount ?? 0;
    @endphp
    @if(!$Appointment->converted_invoice)
    <div class="action-btn" style="margin-left: 10px;">
        {!! Form::open([
        'method' => 'get',
        'route' => ['invoice.convert.appointment', $Appointment->id],
        'id' => 'proposal-form-' . $Appointment->id,
        ]) !!}
        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-success"
            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Convert to Invoice') }}"
            aria-label="{{__('Delete')}}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="proposal-form-{{ $Appointment->id }}">
            <i class="ti ti-exchange text-white"></i>
        </a>
        {{ Form::close() }}
    </div>
    @else
     <div class="action-btn me-2" style="margin-left: 10px;">
            <a href="{{ route('invoice.show', \Crypt::encrypt($Appointment->converted_invoice)) }}" class="mx-3 btn btn-sm align-items-center bg-warning"
                data-bs-toggle="tooltip" title="{{ __('View') }}">
                <i class="ti ti-eye  text-white"></i>
            </a>
        </div>
    @endif
</div>