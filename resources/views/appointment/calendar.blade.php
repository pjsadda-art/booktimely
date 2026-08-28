@extends('layouts.main')

@section('page-title')
    {{ __('Appointment Calendar') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointment Calendar') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('page-action')
    <div class="d-flex col-auto gap-2">
        @stack('addButtonHook')
        @permission('appointment create')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg"
                data-title="{{ __('Create New Appointment') }}" data-url="{{ route('appointment.create') }}"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"><i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/resource-timegrid@6.1.10/index.global.min.css" rel="stylesheet">
    <style>
        .fc-timegrid-event-short .fc-event-main-frame {
            overflow: visible;
        }
        .fc-col-header {
            width: 100% !important;
        }
        .calendar .fc-scrollgrid{
            width: 100% !important;
        }
        .fc-v-event .fc-event-time {
            overflow: visible;
        }

        .fc-event,
        .fc-event:not([href]) {
            padding: unset;
        }

        .fc-resourceTimeGridDay-button {
            display: none !important;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-lg-12 col-12">
            <div class="card appointment-calendar-wrp">
                <div class="card-header">
                    <div class="row row-gaps justify-content-between align-items-center">
                        <div class="col-xxl-auto col-12 ">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>

                        <div class="col-xxl-auto col-12">
                            {{ Form::open(['route' => ['appointment.calendar'], 'method' => 'GET', 'id' => 'calendar_submit']) }}
                            <div class="gap-3 d-flex flex-wrap align-items-center justify-content-xxl-end justify-content-start">

                                {{ Form::label('calendar type', __('Calendar Type'), ['class' => 'text-type h6 mb-0']) }}
                                <div class="flex-wrap gap-2 d-flex radio-check">
                                    <div class="mb-0 form-check">
                                        <input type="radio" id="local_calendar" value="local_calendar"
                                            name="calendar_type" class="form-check-input code"
                                            {{ !isset($_GET['calendar_type']) || $_GET['calendar_type'] == 'local_calendar' ? "checked='checked'" : '' }}>
                                        <label class="form-check-label"
                                            for="local_calendar">{{ __('Local Calendar') }}</label>
                                    </div>
                                    @if (module_is_active('GoogleCalendar') && isset($company_settings['google_calendar_enable']))
                                        @if (isset($company_settings['google_calendar_enable']) && $company_settings['google_calendar_enable'] == 'on')
                                            <div class="mb-0 form-check">
                                                <input type="radio" id="google_calendar" value="google_calendar"
                                                    name="calendar_type" class="form-check-input code"
                                                    {{ isset($_GET['calendar_type']) && $_GET['calendar_type'] == 'google_calendar' ? "checked='checked'" : '' }}>
                                                <label class="form-check-label"
                                                    for="google_calendar">{{ __('Google Calendar') }}</label>
                                            </div>
                                        @endif
                                    @endif
                                    @if (module_is_active('OutlookCalendar') && isset($company_settings['outlook_calendar_enable']))
                                        @if (isset($company_settings['outlook_calendar_enable']) && $company_settings['outlook_calendar_enable'] == 'on')
                                            <div class="mb-0 form-check">
                                                <input type="radio" id="outlook_calendar" value="outlook_calendar"
                                                    name="calendar_type" class="form-check-input code"
                                                    {{ isset($_GET['calendar_type']) && $_GET['calendar_type'] == 'outlook_calendar' ? "checked='checked'" : '' }}>
                                                <label class="form-check-label"
                                                    for="outlook_calendar">{{ __('Outlook Calendar') }}</label>
                                            </div>
                                        @endif
                                    @endif
                                </div>


                                <div class="d-flex header-btn-wrp">
                                    <a class="btn btn-sm btn-primary me-2"
                                        onclick="document.getElementById('calendar_submit').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('appointment.calendar') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>

                            {{ Form::close() }}
                        </div>

                    </div>
                </div>
                <div class="card-body p-4">
                    <div id='calendar' class='calendar' data-toggle="calendar"></div>
                </div>
            </div>
        </div>
        <!-- <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-0">{{ __('Appointments') }}</h5>
                    <div class="card-body appointment-calendar-list p-0">
                    <ul class="event-cards list-group list-group-flush w-100 p-3">
                        @foreach ($appointments as $appointment)
                            @php
                                $date = !empty($appointment->start)
                                    ? $appointment->start
                                    : $appointment['start']->format('Y-m-d');
                                $end['end'] = Carbon\Carbon::parse($appointment['end']);
                                $month = date('m', strtotime($date));
                            @endphp
                            @if ($month == date('m'))
                                <li class="mb-3 list-group-item card">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-0">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar badge p-2 px-3 bg-primary">
                                                    <i class="ti ti-calendar"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="m-0">
                                                        <div class="fc-daygrid-event sp-inheri">
                                                            <div class="fc-event-title-container">
                                                                <div class="fc-event-title text-dark">
                                                                    {{ $date }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </h6>
                                                    <small class="text-muted">
                                                        {{ !empty($appointment->time)
                                                            ? $appointment->time
                                                            : \Carbon\Carbon::parse($appointment['start'])->format('H:i') .
                                                                '-' .
                                                                \Carbon\Carbon::parse($appointment['end'])->format('H:i') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.10/index.global.min.js"></script>
    <script type="text/javascript">
        (function() {
            const resource = JSON.parse('<?php echo json_encode($staffs); ?>');
            const rawEvents = {!! json_encode($appointments) !!};
            // Map staff_id to resourceId so events are placed in the correct staff column
            const events = rawEvents.map(function(e) {
                return Object.assign({}, e, { resourceId: String(e.staff_id) });
            });
            console.log('loading calendar', events);
            var checkedValue = $('input[name="calendar_type"]:checked').val();
            var etitle;
            var etype;
            var etypeclass;
            var locale = "{{ app()->getLocale() }}";
            var calendar = window.appointmentCalendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,resourceTimeGridDay'
                },
                buttonText: {
                    timeGridDay: "{{ __('Day') }}",
                    timeGridWeek: "{{ __('Week') }}",
                    dayGridMonth: "{{ __('Month') }}",
                    resourceTimeGridDay: "{{ __('Resource Day') }}"
                },
                locale: locale,
                //themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                slotMinTime: "{{ $slotMinTime }}",
                slotMaxTime: "{{ $slotMaxTime }}",
                scrollTime: "{{ $slotMinTime }}",
                businessHours: {!! json_encode($calendarBusinessHours) !!},
                nowIndicator: true,
                navLinks: true,
                droppable: true,
                selectable: true,
                initialView: 'resourceTimeGridDay',
                resourceAreaWidth: '15%',
                resourceAreaColumns: [{
                    headerContent: 'Staffs',
                    field: 'title'
                }],
                refetchResourcesOnNavigate: true,
                resourceOrder: 'title', // when occupancy tied, order by title
                resources: resource,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                allDaySlot: false,
                firstDay: {{ $weekStartDay }},
                events: function(info, successCallback, failureCallback){
                    $.ajax({
                        url: "{{ route('appointment.calendar.events') }}",
                        dataType: 'json',
                        success: function(data){ successCallback(data); },
                        error: function(){ successCallback(events); }
                    });
                },
                dateClick: function(info) {
                    openCreateAppointmentAt(info.date, info.resource ? info.resource.id : null);
                },
                select: function(info) {
                    openCreateAppointmentAt(info.start, info.resource ? info.resource.id : null);
                    calendar.unselect();
                },
                eventClick: function(e) {
                    e.jsEvent.preventDefault();
                    var title = e.title;
                    var url = e.el.href;
                    var size = 'md';
                    $("#commonModal .modal-title").html('Appointment Details');
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {
                            $('#commonModal .body').html(data);
                            $("#commonModal").modal('show');
                            if (checkedValue === 'google_calendar') {
                                $("#commonModal").modal('hide');
                            } else if (checkedValue === 'outlook_calendar') {
                                $("#commonModal").modal('hide');
                            }
                            common_bind();
                        },
                        error: function(data) {
                            data = data.responseJSON;
                            toastrs('Error', data.error, 'error')
                        }
                    });
                }

            });
            calendar.render();

            function pad2(n){ n = String(n); return n.length < 2 ? '0' + n : n; }
            function formatDateDMY(d){ return pad2(d.getDate()) + '-' + pad2(d.getMonth()+1) + '-' + d.getFullYear(); }
            function formatTimeHM(d){ return pad2(d.getHours()) + ':' + pad2(d.getMinutes()); }

            window._prefillAppointment = null;

            function openCreateAppointmentAt(date, resourceId){
                var hasTime = !(date.getHours() === 0 && date.getMinutes() === 0);
                var params = [];
                params.push('date=' + encodeURIComponent(formatDateDMY(date)));
                if (hasTime) params.push('time=' + encodeURIComponent(formatTimeHM(date)));
                if (resourceId) params.push('staff_id=' + encodeURIComponent(resourceId));
                var url = "{{ route('appointment.calendar.create') }}" + '?' + params.join('&');

                var $a = $('<a href="#" data-ajax-popup="true" data-size="lg" ' +
                    'data-title="{{ __('Create New Appointment') }}"></a>')
                    .attr('data-url', url)
                    .css('display','none')
                    .appendTo('body');
                $a.trigger('click');
                setTimeout(function(){ $a.remove(); }, 500);
            }

            function applyPrefillToCreateModal(){
                var pf = window._prefillAppointment;
                if (!pf) return;

                var formDeadline = Date.now() + 5000;
                var formWait = setInterval(function(){
                    var $form = $('#appointment-form-date');
                    if (!$form.length) {
                        if (Date.now() > formDeadline) clearInterval(formWait);
                        return;
                    }
                    clearInterval(formWait);
                    doPrefill($form, pf);
                    window._prefillAppointment = null;
                }, 100);
            }

            function doPrefill($form, pf){
                if (!$form.find('input[name="from_calendar"]').length) {
                    $form.append('<input type="hidden" name="from_calendar" value="1">');
                }

                if (pf.staffId) {
                    var $staff = $form.find('select[name="staff"], #staff');
                    if ($staff.length && $staff.find('option[value="' + pf.staffId + '"]').length) {
                        $staff.val(pf.staffId).trigger('change');
                    }
                }

                var $dp = $form.find('#datepicker, input[name="appointment_date"]');
                if ($dp.length) {
                    var parts = pf.date.split('-');
                    var jsDate = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                    $dp.val(pf.date);
                    try {
                        if ($.fn.datepicker) {
                            $dp.datepicker('setDate', jsDate);
                            $dp.datepicker('update', jsDate);
                        }
                    } catch(err) {}
                    $dp.trigger('change');
                    $dp.trigger('changeDate', {date: jsDate, format: function(){ return pf.date; }});
                    if (typeof updateAppointment === 'function') {
                        try { updateAppointment($('.service').val()); } catch(e) {}
                    }
                }

                if (pf.time) {
                    var hm = pf.time.split(':');
                    var $hours = $form.find('#hours, input[name="hours"]');
                    var $mins  = $form.find('#minutes, input[name="minutes"]');
                    if ($hours.length) $hours.prop('disabled', false).val(hm[0]);
                    if ($mins.length)  $mins.prop('disabled', false).val(hm[1]);

                    var targetTime = pf.time;
                    var deadline = Date.now() + 10000;
                    var picked = false;
                    var iv = setInterval(function(){
                        if (picked || Date.now() > deadline) { clearInterval(iv); return; }
                        var $radios = $('#timeSlotsContainer input[type="radio"][name="duration"]');
                        if (!$radios.length) return;
                        $radios.each(function(){
                            var v = $(this).attr('value') || '';
                            if (v.indexOf(targetTime) === 0) {
                                $(this).prop('checked', true).trigger('change').trigger('click');
                                picked = true;
                                return false;
                            }
                        });
                        if (picked) clearInterval(iv);
                    }, 200);
                }
            }

            $(document).on('shown.bs.modal', '#commonModal', function(){
                setTimeout(function(){
                    var $form = $('#appointment-form-date');
                    if ($form.length && !$form.find('input[name="from_calendar"]').length) {
                        $form.append('<input type="hidden" name="from_calendar" value="1">');
                    }
                    applyPrefillToCreateModal();
                }, 150);
            });
        })();
    </script>
@endpush
