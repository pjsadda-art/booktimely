<div class="row">
    <div class="row">


        {{-- Aws --}}
        @if($sms_setting == "sns")
            <div class="form-group col-md-4">
                <label for="sns_access_key" class="form-label">{{ __('SNS Access Key') }}</label>
                {{ Form::text('sns_access_key', isset($settings['sns_access_key']) ? $settings['sns_access_key'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter AWS SNS Access Key'), 'id' => 'sns_access_key',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled'] ) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sns_secret_key" class="form-label">{{ __('SNS Secret Key') }}</label>
                {{ Form::text('sns_secret_key', isset($settings['sns_secret_key']) ? $settings['sns_secret_key'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter AWS SNS Secret Key'), 'id' => 'sns_secret_key',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sns_region" class="form-label">{{ __('AWS SNS Region') }}</label>
                {{ Form::text('sns_region', isset($settings['sns_region']) ? $settings['sns_region'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter AWS SNS Region'), 'id' => 'sns_region',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sns_sender_id" class="form-label">{{ __('AWS SNS Sender ID') }}</label>
                {{ Form::text('sns_sender_id', isset($settings['sns_sender_id']) ? $settings['sns_sender_id'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter AWS SNS Sender ID'), 'id' => 'sns_sender_id',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sns_type" class="form-label">{{ __('AWS Type') }}</label>
                {{ Form::text('sns_type', isset($settings['sns_type']) ? $settings['sns_type'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Type'), 'id' => 'sns_type',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>


            {{-- twilio --}}
        @elseif ($sms_setting == "twilio")
            <div class="form-group col-md-4">
                <label for="sms_twilio_sid" class="form-label">{{ __('Twilio Sid') }}</label>
                {{ Form::text('sms_twilio_sid', isset($settings['sms_twilio_sid']) ? $settings['sms_twilio_sid'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Twilio Sid'), 'id' => 'sms_twilio_sid',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sms_twilio_token" class="form-label">{{ __('Twilio Token') }}</label>
                {{ Form::text('sms_twilio_token', isset($settings['sms_twilio_token']) ? $settings['sms_twilio_token'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Twilio Token'), 'id' => 'sms_twilio_token',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-4">
                <label for="sms_twilo_from_number" class="form-label">{{ __('Twilio From Number') }}</label>
                {{ Form::text('sms_twilo_from_number', isset($settings['sms_twilo_from_number']) ? $settings['sms_twilo_from_number'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Twilio From Number'), 'id' => 'sms_twilo_from_number',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>


        {{--clockwork  --}}
        @elseif ($sms_setting == "clockwork")
        <div class="form-group col-md-6">
            <label for="clockwork_api_key" class="form-label">{{ __('Clockwork API Key') }}</label>
            {{ Form::text('clockwork_api_key', isset($settings['clockwork_api_key']) ? $settings['clockwork_api_key'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Clockwork API Key'), 'id' => 'clockwork_api_key',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>


      {{-- melipayamak  --}}
        @elseif ($sms_setting == "melipayamak")
        <div class="form-group col-md-4">
            <label for="melipayamak_username" class="form-label">{{ __('Melipayamak Username') }}</label>
            {{ Form::text('melipayamak_username', isset($settings['melipayamak_username']) ? $settings['melipayamak_username'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Melipayamak Username'), 'id' => 'melipayamak_username',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>
        <div class="form-group col-md-4">
            <label for="melipayamak_password" class="form-label">{{ __('Melipayamak Password') }}</label>
            {{ Form::text('melipayamak_password', isset($settings['melipayamak_password']) ? $settings['melipayamak_password'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Melipayamak Password'), 'id' => 'melipayamak_password',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>
        <div class="form-group col-md-4">
            <label for="melipayamak_from_number" class="form-label">{{ __('From Number') }}</label>
            {{ Form::text('melipayamak_from_number', isset($settings['melipayamak_from_number']) ? $settings['melipayamak_from_number'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Melipayamak From Number'), 'id' => 'melipayamak_from_number',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>


        {{-- kavenegar --}}
        @elseif ($sms_setting == "kavenegar")
        <div class="form-group col-md-6">
            <label for="kavenegar_apiKey" class="form-label">{{ __('Kavenegar Api Key') }}</label>
            {{ Form::text('kavenegar_apiKey', isset($settings['kavenegar_apiKey']) ? $settings['kavenegar_apiKey'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Kavenegar Api Key'), 'id' => 'kavenegar_apiKey',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>
        <div class="form-group col-md-6">
            <label for="kavenegar_from_number" class="form-label">{{ __('From Number') }}</label>
            {{ Form::text('kavenegar_from_number', isset($settings['kavenegar_from_number']) ? $settings['kavenegar_from_number'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Kavenegar From Number'), 'id' => 'kavenegar_from_number',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>


        {{-- smsgatewayme --}}
        @elseif ($sms_setting == "smsgatewayme")
        <div class="form-group col-md-6">
            <label for="smsgatewayme_apiToken" class="form-label">{{ __('SMS Gateway Me Api Token') }}</label>
            {{ Form::text('smsgatewayme_apiToken', isset($settings['smsgatewayme_apiToken']) ? $settings['smsgatewayme_apiToken'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Api Token'), 'id' => 'smsgatewayme_apiToken',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>
        <div class="form-group col-md-6">
            <label for="Smsgatewayme_device_id" class="form-label">{{ __('Default Device ID') }}</label>
            {{ Form::text('Smsgatewayme_device_id', isset($settings['Smsgatewayme_device_id']) ? $settings['Smsgatewayme_device_id'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Default Device ID'), 'id' => 'Smsgatewayme_device_id',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
        </div>
        
         {{-- cellcast --}}
         @elseif ($sms_setting == "cellcast")
         <div class="form-group col-md-6">
             <label for="cellcast_apiKey" class="form-label">{{ __('Cellcast Api Key') }}</label>
             {{ Form::text('cellcast_apiKey', isset($settings['cellcast_apiKey']) ? $settings['cellcast_apiKey'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Cellcast Api Key'), 'id' => 'cellcast_apiKey',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
            <div class="form-group col-md-6">
                <label for="cellcast_from_number" class="form-label">{{ __('From Number') }}</label>
                {{ Form::text('cellcast_from_number', isset($settings['cellcast_from_number']) ? $settings['cellcast_from_number'] : null,  ['class' => 'form-control', 'placeholder' => __('Enter Cellcast From Number'), 'id' => 'cellcast_from_number',isset($settings['sms_notification_is']) && $settings['sms_notification_is'] == 'on' ? '' : ' disabled']) }}
            </div>
        @endif
    </div>

</div>
