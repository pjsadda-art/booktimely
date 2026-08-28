<style>
    .ui-autocomplete { z-index: 9999 !important; background:#fff; border:1px solid #ccc; font-size:14px; box-shadow:0 2px 8px rgba(0,0,0,0.15); max-height:300px; overflow-y:auto; border-radius:6px; list-style:none; padding:4px 0; }
    .ui-menu-item-wrapper { padding:8px 14px; cursor:pointer; }
    .ui-menu-item-wrapper.ui-state-active { background:#0d6efd; color:#fff; }
    .ui-autocomplete li { list-style:none; }
    .cal-time-wrap { display:flex; gap:8px; align-items:stretch; }
    .cal-time-wrap input[type="time"] { flex:1; }
    .cal-quick-times { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
    .cal-quick-times button { font-size:12px; padding:4px 10px; border:1px solid #d0d5dd; background:#f8f9fa; border-radius:6px; cursor:pointer; }
    .cal-quick-times button:hover { background:#e9ecef; }
    .cal-quick-times button.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
    .cal-summary { background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:13px; }
    .cal-summary strong { color:#0d6efd; }
</style>

{{ Form::open(['route' => 'appointment.calendar.store', 'method' => 'post', 'data-url' => route('appointment.duration'), 'id' => 'appointment-form-date', 'class' => 'needs-validation', 'novalidate']) }}
{!! Form::hidden('from_calendar', '1') !!}
{!! Form::hidden('appointment_status', 'Pending') !!}

<div class="modal-body">

    @if ($prefillDate || $prefillTime)
        <div class="cal-summary">
            {{ __('Booking for') }}:
            @if ($prefillDate) <strong>{{ $prefillDate }}</strong> @endif
            @if ($prefillTime) {{ __('at') }} <strong>{{ $prefillTime }}</strong> @endif
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}
                {{ Form::select('service', $service, null, ['class' => 'form-control service', 'required' => 'required', 'id' => 'service']) }}
            </div>
        </div>
        <div class="col-md-6 appoinment-customer-info">
            <div class="form-group">
                {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                <input type="text" id="customer-autocomplete" name="customer_name" class="form-control" placeholder="{{ __('Search customer by name or mobile') }}" autocomplete="new-password" required>
                <input type="hidden" id="customer-id" name="customer" value="">
                @permission('customer manage')
                <div class="text-xs mt-1">{{ __('Create customer here. ') }}
                    <a href="#" class="btn btn-sm btn-primary" data-ajax-popup-customer="true" data-size="md"
                        data-title="{{ __('Create New Customer') }}" data-url="{{ route('customer.ajax.create') }}">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
                @endpermission
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                {{ Form::select('location', $location, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}
                {{ Form::select('staff', $staff, $prefillStaff, ['class' => 'form-control', 'required' => 'required', 'id' => 'staff']) }}
            </div>
        </div>

        @if (!empty($custom_field) && $custom_field == 'on')
            @foreach ($custom_fields as $cf)
                <div class="col-md-3 form-group">
                    @if (in_array($cf->type, ['textfield','textarea','date','number']))
                        <label for="{{ $cf->label }}" class="form-label">{{ $cf->label }}</label>
                    @endif
                    @if ($cf->type === 'textfield')
                        <input type="text" id="{{ $cf->label }}" name="values[textfield][{{ $cf->label }}]" placeholder="Value" class="form-control">
                    @elseif($cf->type === 'textarea')
                        <textarea name="values[textarea][{{ $cf->label }}]" placeholder="Value" class="form-control"></textarea>
                    @elseif($cf->type === 'date')
                        <input type="date" class="form-control" name="values[date][{{ $cf->label }}]">
                    @elseif($cf->type === 'number')
                        <input type="number" class="form-control" name="values[number][{{ $cf->label }}]" placeholder="Value">
                    @endif
                </div>
            @endforeach
        @endif

        <div class="col-md-4">
            <div class="form-group">
                <label for="appointment_date" class="form-label">{{ __('Appointment Date') }}</label>
                <div class="input-group date">
                    <input class="form-control datepicker p-2 px-3" type="text" id="datepicker" name="appointment_date"
                        placeholder="DD-MM-YYYY" autocomplete="off" required="required"
                        value="{{ $prefillDate }}"
                        data-dates="{{ json_encode($combinedArray) }}"
                        data-holiday="{{ json_encode($businesholiday) }}">
                    <span class="input-group-text"><i class="feather icon-calendar"></i></span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label">{{ __('Start Time') }}</label>
                @php
                    $h24 = is_numeric($prefillHour) ? (int)$prefillHour : 9;
                    $m24 = is_numeric($prefillMin)  ? (int)$prefillMin  : 0;
                    $period = $h24 >= 12 ? 'PM' : 'AM';
                    $h12 = $h24 % 12; if ($h12 === 0) $h12 = 12;
                @endphp
                <div class="cal-time-wrap">
                    <select id="time_hour" class="form-control" style="max-width:90px;">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @if($i === $h12) selected @endif>{{ str_pad($i,2,'0',STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                    <select id="time_min" class="form-control" style="max-width:90px;">
                        @for ($i = 0; $i < 60; $i += 5)
                            <option value="{{ $i }}" @if($i === ((int)floor($m24/5)*5)) selected @endif>{{ str_pad($i,2,'0',STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                    <select id="time_period" class="form-control" style="max-width:90px;">
                        <option value="AM" @if($period==='AM') selected @endif>AM</option>
                        <option value="PM" @if($period==='PM') selected @endif>PM</option>
                    </select>
                </div>
                <div class="cal-quick-times" id="quickTimes">
                    <button type="button" data-t="-15">−15m</button>
                    <button type="button" data-t="-5">−5m</button>
                    <button type="button" data-t="+5">+5m</button>
                    <button type="button" data-t="+15">+15m</button>
                    <button type="button" data-t="+30">+30m</button>
                </div>
                {{ Form::hidden('hours', $prefillHour !== '' ? $prefillHour : $h24, ['id' => 'hours']) }}
                {{ Form::hidden('minutes', $prefillMin !== '' ? $prefillMin : $m24, ['id' => 'minutes']) }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="duration" class="form-label">{{ __('Duration (minutes)') }}</label>
                {{ Form::text('durations', null, ['class' => 'form-control', 'id' => 'duration', 'placeholder' => __('e.g. 30'), 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                {{ Form::text('price', null, ['class' => 'form-control', 'id' => 'price', 'placeholder' => __('Enter price')]) }}
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-group">
                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    <div class="modal-footer p-0 pt-3 gap-3">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Create Appointment'), ['class' => 'btn m-0 btn-primary']) }}
    </div>
</div>
{{ Form::close() }}

<script>
(function(){
    function pad2(n){ n = String(n); return n.length < 2 ? '0' + n : n; }
    function syncHM(){
        var h = parseInt($('#time_hour').val(), 10) || 12;
        var m = parseInt($('#time_min').val(), 10) || 0;
        var p = $('#time_period').val();
        if (p === 'AM') { if (h === 12) h = 0; }
        else            { if (h !== 12) h = h + 12; }
        $('#hours').val(pad2(h));
        $('#minutes').val(pad2(m));
    }
    function setFromHM(h24, m24){
        var p = h24 >= 12 ? 'PM' : 'AM';
        var h12 = h24 % 12; if (h12 === 0) h12 = 12;
        var m5 = Math.round(m24/5)*5; if (m5 >= 60) m5 = 55;
        $('#time_hour').val(h12);
        $('#time_min').val(m5);
        $('#time_period').val(p);
        syncHM();
    }
    $('#time_hour, #time_min, #time_period').on('change', syncHM);
    syncHM();

    // AJAX submit — no page refresh, add event live to calendar
    $('#appointment-form-date').on('submit', function(e){
        e.preventDefault();
        syncHM();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"], input[type="submit"]').prop('disabled', true);
        // Ensure required hidden inputs are present
        if (!$('#customer-id').val()) {
            if (typeof toastrs === 'function') toastrs('Error', 'Please select a customer from the suggestions.', 'error');
            $btn.prop('disabled', false);
            return;
        }
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(res){
                if (res && res.success) {
                    $('#commonModal').modal('hide');
                    var cal = window.appointmentCalendar;
                    if (cal) {
                        try {
                            cal.refetchEvents();
                            if (res.event && res.event.start) {
                                cal.gotoDate(res.event.start);
                            }
                        } catch(err) { console.error('refetch failed', err); }
                    }
                    if (typeof toastrs === 'function') toastrs('Success', res.message || 'Appointment created', 'success');
                } else {
                    if (typeof toastrs === 'function') toastrs('Error', (res && res.message) || 'Failed', 'error');
                }
            },
            error: function(xhr){
                var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed to create appointment';
                if (typeof toastrs === 'function') toastrs('Error', msg, 'error');
            },
            complete: function(){ $btn.prop('disabled', false); }
        });
    });

    $('#quickTimes button').on('click', function(){
        var delta = parseInt($(this).data('t'), 10);
        var total = (parseInt($('#hours').val(),10)||0)*60 + (parseInt($('#minutes').val(),10)||0) + delta;
        if (total < 0) total += 24*60;
        total = total % (24*60);
        setFromHM(Math.floor(total/60), total%60);
    });

    // Customer autocomplete
    $('#customer-autocomplete').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/api/customers/search',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (Array.isArray(data) && data.length && data[0].id) {
                        response($.map(data, function(item){
                            return { label: item.text, value: item.text, id: item.id };
                        }));
                    } else { response([]); }
                }
            });
        },
        minLength: 1,
        select: function(event, ui){ $('#customer-id').val(ui.item.id); },
        appendTo: 'body'
    });

    // Auto-fill price & duration from service when chosen (mirrors site default if exposed).
    $('#service').on('change', function(){
        var sid = $(this).val();
        if (!sid) return;
        $.ajax({
            url: $('#appointment-form-date').data('url'),
            type: 'POST',
            data: { service_id: sid, _token: '{{ csrf_token() }}' },
            success: function(res){
                if (res && typeof res === 'object') {
                    if (res.duration && !$('#duration').val()) $('#duration').val(res.duration);
                    if (res.price && !$('#price').val()) $('#price').val(res.price);
                }
            }
        });
    });
})();
</script>
