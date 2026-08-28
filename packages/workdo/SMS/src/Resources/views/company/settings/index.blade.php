<div class="card" id="sms-sidenav">
    {{ Form::open(['route' => 'sms.setting.save', 'method' => 'post']) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('SMS Settings') }}</h5>
                <small>{{ __('Edit your SMS settings') }}</small>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 text-end">
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="sms_notification_is" class="form-check-input input-primary"
                        id="sms_notification_is"
                        {{ (isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on') ? ' checked ' : '' }}>
                    <label class="form-check-label" for="sms_notification_is"></label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body pb-0">
        <div class="d-flex">
            <div class="col-sm-6 col-12">
<?php //echo "<pre>"; print_r($settings); echo "</pre>"; ?>
                <div class="form-group col switch-width">
                    {{ Form::label('sms_setting', __('SMS Setting'), ['class' => ' col-form-label']) }}

                    {{ Form::select('sms_setting', $sms_setting, isset($settings['sms_setting']) ? $settings['sms_setting'] : '', ['id' => 'sms_setting', 'class' => 'form-control choices', 'searchEnabled' => 'true']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12" id="getsmsfields">
            </div>
        </div>
        <h5 class="mb-4">{{ __('Notifications') }}</h5>
        <ul class="nav nav-pills gap-2 mb-3" id="pills-tab" role="tablist">
            @php
                $active = 'active';
            @endphp
            @foreach ($notification_modules as $module)
                @if (module_is_active($module) || $module == 'general')
                    @if (Laratrust::hasPermission(strtolower($module) . ' manage') || $module == 'general')
                        <li class="nav-item">
                            <a class="nav-link text-capitalize {{ $active }}"
                                id="sms-{{ strtolower($module) }}-tab" data-bs-toggle="pill"
                                href="#sms-{{ strtolower($module) }}" role="tab"
                                aria-controls="sms-{{ strtolower($module) }}"
                                aria-selected="true">{{ Module_Alias_Name($module) }}</a>
                        </li>
                        @php
                            $active = '';
                        @endphp
                    @endif
                @endif
            @endforeach
        </ul>
        <div class="tab-content" id="sms-tabContent">
            @php
                $active = 'active';
            @endphp
            @foreach ($notification_modules as $module)
                @if (module_is_active($module) || $module == 'general')
                    <div class="tab-pane fade {{ $active }} show" id="sms-{{ strtolower($module) }}"
                        role="tabpanel" aria-labelledby="sms-{{ strtolower($module) }}-tab">
                        <div class="row">
                            @foreach ($notify as $action)
                                @if (Laratrust::hasPermission($action->permissions ?? '') || Laratrust::hasPermission(strtolower($module) . ' manage') || $action->permissions == null)
                                    @if ($action->module == $module)
                                        <div class="col-md-6">
                                            <div
                                                class="d-flex align-items-center justify-content-between list_colume_notifi pb-2 mb-3">
                                                <div class="mb-3 mb-sm-0">
                                                    <h6>
                                                        <label for="{{ $action->action }}"
                                                            class="form-label">{{ $action->action }}</label>
                                                    </h6>
                                                </div>
                                                <div class="text-end">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="hidden"
                                                            name="sms[{{ 'SMS ' . $action->action }}]"
                                                            value="0" />
                                                        <input class="form-check-input"
                                                            {{ (isset($settings['SMS ' . $action->action]) && $settings['SMS ' . $action->action] == true) ? 'checked' : '' }}
                                                            id="sms_project_notificaation"
                                                            name="sms[{{ 'SMS ' . $action->action }}]"
                                                            type="checkbox" value="1">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @php
                        $active = '';
                    @endphp
                @endif
            @endforeach
            <div class="row">
            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="reminder_trigger" class="form-label">Trigger SMS for status</label>
                                                        <select class="form-control" 
                                                            id="reminder_trigger"
                                                            name="appointment_reminder_trigger">
                                                            <option value="">{{ __('Select Status') }}</option>
                                                          @foreach($custom_status as $status)
                                                            <option value="{{ $status->id }}" {{ (isset($settings['appointment_reminder_trigger']) && $settings['appointment_reminder_trigger'] == $status->id) ? 'selected' : '' }}>{{ $status->title }}</option>
                                                          @endforeach
                                                        </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="webhook_update_status" class="form-label">Webhook update status</label>
                                                        <select class="form-control" 
                                                            id="webhook_update_status"
                                                            name="webhook_update_status">
                                                          <option value="">{{ __('Select Status') }}</option>
                                                          @foreach($custom_status as $status)
                                                            <option value="{{ $status->id }}" {{ (isset($settings['webhook_update_status']) && $settings['webhook_update_status'] == $status->id) ? 'selected' : '' }}>{{ $status->title }}</option>
                                                          @endforeach  
                                                        </select>
                                                </div>
                                            </div>
                                            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}

</div>

<!--Bank Accounts Settings-->
<script>
    $(document).ready(function() {

        getinput()
    });
    $(document).on('change', '#sms_setting', function() {
        getinput()
    });

    function getinput() {

        var sms_setting = $('#sms_setting').val();
        $.ajax({
            url: '{{ route('get.sms.fields') }}',
            type: 'POST',

            data: {
                "_token": "{{ csrf_token() }}",
                "sms_setting": sms_setting,
            },
            success: function(data) {
                $('#getsmsfields').empty();
                $('#getsmsfields').append(data.html)
                $('.email').append(data.html)
            },

        });
    }

</script>
<script>

    $(document).on('click', '#sms_notification_is', function() {
        if ($('#sms_notification_is').prop('checked')) {
            $("#sms_setting").removeAttr("disabled");
            $("#sns_access_key").removeAttr("disabled");
            $("#sns_secret_key").removeAttr("disabled");
            $("#sns_sender_id").removeAttr("disabled");
            $("#sns_type").removeAttr("disabled");
            $("#sns_region").removeAttr("disabled");
            $("#sms_twilio_sid").removeAttr("disabled");
            $("#sms_twilio_token").removeAttr("disabled");
            $("#sms_twilo_from_number").removeAttr("disabled");
            $("#clockwork_api_key").removeAttr("disabled");
            $("#melipayamak_username").removeAttr("disabled");
            $("#melipayamak_password").removeAttr("disabled");
            $("#melipayamak_from_number").removeAttr("disabled");
            $("#kavenegar_apiKey").removeAttr("disabled");
            $("#kavenegar_from_number").removeAttr("disabled");
            $("#smsgatewayme_apiToken").removeAttr("disabled");
            $("#Smsgatewayme_device_id").removeAttr("disabled");
            $("#cellcast_apiKey").removeAttr("disabled");
            $("#cellcast_from_number").removeAttr("disabled");

        } else {
            $("#sms_setting").attr("disabled", "disabled");
            $("#sns_access_key").attr("disabled", "disabled");
            $("#sns_secret_key").attr("disabled", "disabled");
            $("#sns_sender_id").attr("disabled", "disabled");
            $("#sns_region").attr("disabled", "disabled");
            $("#sns_type").attr("disabled", "disabled");
            $("#sms_twilio_sid").attr("disabled", "disabled");
            $("#sms_twilio_token").attr("disabled", "disabled");
            $("#sms_twilo_from_number").attr("disabled", "disabled");
            $("#clockwork_api_key").attr("disabled", "disabled");
            $("#melipayamak_username").attr("disabled", "disabled");
            $("#melipayamak_password").attr("disabled", "disabled");
            $("#melipayamak_from_number").attr("disabled", "disabled");
            $("#kavenegar_apiKey").attr("disabled", "disabled");
            $("#kavenegar_from_number").attr("disabled", "disabled");
            $("#smsgatewayme_apiToken").attr("disabled", "disabled");
            $("#Smsgatewayme_device_id").attr("disabled", "disabled");
            $("#cellcast_apiKey").attr("disabled", "disabled");
            $("#cellcast_from_number").attr("disabled", "disabled");
        }
    });
</script>
