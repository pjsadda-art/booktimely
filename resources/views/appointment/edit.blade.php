{{ Form::model($appointment, ['route' => ['appointment.update', $appointment->id], 'method' => 'PUT', 'id' => 'appointment-form-date', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate', 'data-url' => route('appointment.duration')]) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}
                {{ Form::select('service', $service, $appointment->service_id, ['class' => 'form-control service', 'required' => 'required', 'id' => 'service']) }}
                @permission('business update')
                    <div class=" text-xs mt-1">{{ __('Create service here. ') }}
                        <a href="{{ route('manage.business') }}"><b>{{ __('Create service') }}</b></a>
                    </div>
                @endpermission
                @error('service')
                    <small class="invalid-service" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group appoinment-customer-info">
                {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}

                @stack('customer_booking')

                {{ Form::select('customer', $customer, $appointment->customer_id, ['class' => 'form-control choices', 'searchEnabled' => 'true']) }}
                @permission('customer manage')
                    <div class=" text-xs mt-1">{{ __('Create customer here. ') }}
                        <a href="{{ route('customer.index') }}"><b>{{ __('Create customer') }}</b></a>
                    </div>
                @endpermission
                @error('customer')
                    <small class="invalid-customer" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                {{ Form::select('location', $location, $appointment->location_id, ['class' => 'form-control', 'required' => 'required']) }}
                @permission('business update')
                    <div class=" text-xs mt-1">{{ __('Create location here. ') }}
                        <a href="{{ route('manage.business') }}"><b>{{ __('Create location') }}</b></a>
                    </div>
                @endpermission
                @error('location')
                    <small class="invalid-location" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}
                {{ Form::select('staff', $staff, $appointment->staff_id, ['class' => 'form-control', 'required' => 'required']) }}
                @permission('business update')
                    <div class=" text-xs mt-1">{{ __('Create staff here. ') }}
                        <a href="{{ route('manage.business') }}"><b>{{ __('Create staff') }}</b></a>
                    </div>
                @endpermission
                @error('staff')
                    <small class="invalid-staff" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        @if ((!empty($custom_field) && $custom_field == 'on'))
        @if (!empty($custom_field) && $custom_field == 'on')
            @php
                $savedCustomValues = json_decode($appointment->custom_field);
            @endphp
            @foreach ($custom_fields as $custom_field)
            @php
                $savedValue = ($savedCustomValues && isset($savedCustomValues->{$custom_field->label}))
                    ? $savedCustomValues->{$custom_field->label}
                    : '';
            @endphp
            <div class="col-md-3 form-group">
                @if (
                $custom_field->type == 'textfield' ||
                $custom_field->type == 'textarea' ||
                $custom_field->type == 'date' ||
                $custom_field->type === 'number')
                <label
                    for={{ $custom_field->label }} class="form-label">{{ $custom_field->label }} </label>
                @endif
                @if ($custom_field->type === 'textfield')
                <input type="text"
                    id = {{ $custom_field->label }}
                    name="values[textfield][{{ $custom_field->label }}]"
                    placeholder="Value"
                    value="{{ $savedValue }}"
                    class="form-control">
                @elseif($custom_field->type === 'textarea')
                <textarea name="values[textarea][{{ $custom_field->label }}]" placeholder="Value" class="form-control">{{ $savedValue }}</textarea>
                @elseif($custom_field->type === 'date')
                <input type="date" class="form-control"
                    name="values[date][{{ $custom_field->label }}]"
                    value="{{ $savedValue }}">
                @elseif($custom_field->type === 'number')
                <input type="number" class="form-control"
                    name="values[number][{{ $custom_field->label }}]"
                    placeholder="Value"
                    value="{{ $savedValue }}">
                @endif
            </div>
            @endforeach
        @stack('view_additional_field')
        @endif
        @if (!empty($custom_field) && $custom_field == 'on')
        <div class="row">
            <div class="col-sm-3 col-12">
                <div class="form-group">
                    {{ Form::label('attachment', __('Attachment')) }}
                    <div class="chose-files" id="myfile">
                        <i class="ti ti-upload px-1"></i>
                        <span>{{ __('Choose file here') }}</span>
                        <input type="file" name="attachment" id="attachment"
                            data-filename="attachment"
                            placeholder="Choose file here">
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
        <div class ="col-md-2">
            <div class="form-group">
                {{ Form::label('durations', __('Duration'), ['class' => 'form-label']) }}
                {{ Form::text('durations', $minutes, ['class' => 'form-control','id'=>'duration', 'placeholder' => __('Enter duration')]) }}
                @error('durations')
                <small class="invalid-durations" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class ="col-md-2">
            <div class="form-group">
                {{ Form::label('hours', __('Hr'), ['class' => 'form-label']) }}
                {{ Form::text('hours', null, ['class' => 'form-control','id'=>'hours', 'disabled'=>'disabled','placeholder' => __('H')]) }}
                @error('hours')
                <small class="invalid-hours" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class ="col-md-2">
            <div class="form-group">
                {{ Form::label('minutes', __('Min'), ['class' => 'form-label']) }}
                {{ Form::text('minutes', null, ['class' => 'form-control','id'=>'minutes', 'disabled'=>'disabled','placeholder' => __('M')]) }}
                @error('minutes')
                <small class="invalid-minutes" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class ="col-md-2">
            <div class="form-group">
                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                {{ Form::text('price', $appointment->payment->amount ?? null, ['class' => 'form-control','id'=>'price', 'placeholder' => __('Enter price')]) }}
                @error('price')
                <small class="invalid-price" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => '4']) }}
                @error('notes')
                    <small class="invalid-notes" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>

        <div class="form-group col-md-6">
            <label for="appointment_date" class="col-form-label pt-0">{{ __('Appointment Date') }}</label>
            <div class="input-group date ">
                <input class="form-control datepicker p-2 px-3" type="text" id="datepicker" name="appointment_date" placeholder="DD-MM-YYYY"
                    autocomplete="off" required="required" value="{{ $appointment->date }}"
                    data-dates={{ json_encode($combinedArray) }}>
                <span class="input-group-text">
                    <i class="feather icon-calendar"></i>
                </span>
            </div>
        </div>

        <div id="timeSlotsContainer">
                    <ul>
                        @php
                            // Determine the selected duration value from the appointment if available
                            $selectedDuration = isset($appointment->time) ? $appointment->time : null;
                        @endphp
                        @foreach ($timeSlots as $key => $timeSlot)
                            @php
                                $slotValue = $timeSlot['start'] . '-' . $timeSlot['end'];
                            @endphp
                            <li class="time-slot-li {{ $selectedDuration == $slotValue ? 'selected-duration' : '' }}">
                                <label>
                                    <input type="radio" name="duration" id="radio{{ $key }}"
                                        value="{{ $slotValue }}" {{ $selectedDuration == $slotValue ? 'checked' : '' }}>
                                    <span>{{ $timeSlot['start'] }}-{{ $timeSlot['end'] }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <style>
                        /* Add some highlight style for the selected duration */
                        .selected-duration {
                            background-color: #e0f7fa;
                            border-radius: 5px;
                            font-weight: bold;
                        }
                    </style>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const radios = document.querySelectorAll('input[name="duration"]');
                            radios.forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    document.querySelectorAll('.time-slot-li').forEach(function(li) {
                                        li.classList.remove('selected-duration');
                                    });
                                    if (this.checked) {
                                        this.closest('li').classList.add('selected-duration');
                                    }
                                });
                            });
                        });
                    </script>
        </div>
        @stack('setting_setup')
    </div>
    <div class="modal-footer pt-3 p-0 gap-3">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Update'), ['class' => 'btn m-0 btn-primary']) }}
    </div>
    {{ Form::close() }}

    <script>
        $(document).ready(function(){
        var minutes = $('#duration').val();
        var hours = Math.floor(minutes / 60);
        var mins = minutes % 60;
        $('#hours').val(hours);
        $('#minutes').val(mins);
})
    </script>
