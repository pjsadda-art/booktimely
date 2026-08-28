<style>
    .ui-autocomplete {
        z-index: 9999 !important;
        background: #fff;
        border: 1px solid #ccc;
        font-size: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        max-height: 300px;
        overflow-y: auto;
        width: 300px !important;
        min-width: 300px;
        border-radius: 6px;
            list-style: none; /* Added to remove bullet points */
    }
    #customer-autocomplete + .ui-autocomplete {
        width: 100% !important;
        min-width: 0;
    }
    .ui-menu-item-wrapper {
        padding: 8px 16px;
        cursor: pointer;
    }
    .ui-menu-item-wrapper.ui-state-active {
        background: #007bff;
        color: #fff;
    }
        .ui-autocomplete li {
            list-style: none; /* Added to remove bullet points from li children */
        }
</style>
{{ Form::open(['url' => 'appointment', 'method' => 'post', 'data-url' => route('appointment.duration'), 'id' => 'appointment-form-date', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}
                {{ Form::select('service', $service, null, ['class' => 'form-control service', 'required' => 'required', 'id' => 'service']) }}
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
        <div class="col-md-6 appoinment-customer-info">
            <div class="form-group">
                {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                @stack('customer_booking')
                <!-- {{ Form::select('customer', $customer, null, ['class' => 'form-control choices', 'searchEnabled' => 'true', 'required' => 'required']) }} -->
                  <input type="text" id="customer-autocomplete" name="customer_name" class="form-control" placeholder="Search customer by name or mobile" autocomplete="new-password" required>
                <input type="hidden" id="customer-id" name="customer" value="">
<script>
                $(function() {
                    $('#customer-autocomplete').autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: '/api/customers/search',
                                dataType: 'json',
                                data: {
                                    q: request.term
                                },
                                success: function(data) {
                                    console.log('AJAX response:', data); // Debug
                                    // Only show customer results
                                    if(Array.isArray(data) && data.length > 0 && data[0].id) {
                                        response($.map(data, function(item) {
                                            return {
                                                label: item.text,
                                                value: item.text,
                                                id: item.id
                                            };
                                        }));
                                    } else {
                                        response([]);
                                    }
                                }
                            });
                        },
                        minLength: 1,
                        select: function(event, ui) {
                            $('#customer-id').val(ui.item.id);
                        },
                        appendTo: 'body' // Fix dropdown not showing in modals
                    });
                });
                </script>
                @permission('customer manage')
                <div class=" text-xs mt-1">{{ __('Create customer here. ') }}
                    <a href="#" class="btn btn-sm btn-primary" data-ajax-popup-customer="true" data-size="md"
                        data-title="{{ __('Create New Customer') }}" data-url="{{ route('customer.ajax.create') }}"
                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                        <i class="ti ti-plus"></i>
                    </a>
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
                {{ Form::select('location', $location, null, ['class' => 'form-control', 'required' => 'required']) }}
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
                {{ Form::select('staff', $staff, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'staff']) }}
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
                @foreach ($custom_fields as $custom_field)
                    <div class="col-md-3 form-group">
                        @if (
                                $custom_field->type == 'textfield' ||
                                $custom_field->type == 'textarea' ||
                                $custom_field->type == 'date' ||
                                $custom_field->type === 'number'
                            )
                            <label for={{ $custom_field->label }} class="form-label">{{ $custom_field->label }}</label>
                        @endif
                        @if ($custom_field->type === 'textfield')
                            <input type="text" id={{ $custom_field->label }} name="values[textfield][{{ $custom_field->label }}]"
                                placeholder="Value" value="{{ $custom_field->value }}" class="form-control">
                        @elseif($custom_field->type === 'textarea')
                            <textarea name="values[textarea][{{ $custom_field->label }}]" placeholder="Value"
                                class="form-control">{{ $custom_field->value }}</textarea>
                        @elseif($custom_field->type === 'date')
                            <input type="date" class="form-control" name="values[date][{{ $custom_field->label }}]"
                                value="{{ $custom_field->value }}">
                        @elseif($custom_field->type === 'number')
                            <input type="number" class="form-control" name="values[number][{{ $custom_field->label }}]"
                                placeholder="Value" value="{{ $custom_field->value }}">
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
                                <input type="file" name="attachment" id="attachment" data-filename="attachment"
                                    placeholder="Choose file here">
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="col-md-2">
            <div class="form-group">
                {{ Form::label('durations', __('Duration'), ['class' => 'form-label']) }}
                {{ Form::text('durations', null, ['class' => 'form-control', 'id' => 'duration', 'placeholder' => __('minutes')]) }}
                @error('durations')
                    <small class="invalid-durations" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {{ Form::label('hours', __('Hr'), ['class' => 'form-label']) }}
                {{ Form::text('hours', null, ['class' => 'form-control', 'id' => 'hours', 'disabled' => 'disabled', 'placeholder' => __('H')]) }}
                @error('hours')
                    <small class="invalid-hours" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {{ Form::label('minutes', __('Min'), ['class' => 'form-label']) }}
                {{ Form::text('minutes', null, ['class' => 'form-control', 'id' => 'minutes', 'disabled' => 'disabled', 'placeholder' => __('M')]) }}
                @error('minutes')
                    <small class="invalid-minutes" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                {{ Form::text('price', null, ['class' => 'form-control', 'id' => 'price', 'placeholder' => __('Enter price')]) }}
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
        {!! Form::hidden('appointment_status', 'Pending') !!}
        <div class="form-group col-md-6">
            <label for="appointment_date" class="col-form-label pt-0">{{ __('Appointment Date') }}</label>
            <div class="input-group date ">
                <input class="form-control datepicker p-2 px-3" type="text" id="datepicker" name="appointment_date"
                    placeholder="DD-MM-YYYY" autocomplete="off" required="required" data-dates={{ json_encode($combinedArray) }} data-holiday={{ json_encode($businesholiday) }}>
                <span class="input-group-text">
                    <i class="feather icon-calendar"></i>
                </span>
            </div>
        </div>
        <div id="timeSlotsContainer"></div>
        @stack('setting_setup')
    </div>
    <div class="modal-footer p-0 pt-3 gap-3">
        <button type="button" class="btn m-0  btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Create'), ['class' => 'btn m-0 btn-primary']) }}
    </div>
    {{ Form::close() }}
</div>