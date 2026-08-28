{{-- WhatsApp Cloud API connection for this business.

     One Meta app serves the whole platform (its webhook URL and app secret
     are set once, platform-wide, in .env) — what's configured here is this
     business's own WhatsApp Business phone number and access token, which
     is how Meta tells inbound replies apart between businesses. --}}
@php
    $value = function ($key, $fallback = '') use ($settings) {
        return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $fallback;
    };

    $tokenSaved = $value('whatsapp_meta_access_token') !== '';
@endphp

<div class="card" id="whatsapp-settings-sidenav">
    <div class="card-header p-3">
        <h5>{{ __('WhatsApp Chat') }}</h5>
        <small class="text-secondary font-weight-bold">
            {{ __('Connect this business\'s WhatsApp Business number so staff can message customers from the appointment side panel.') }}
        </small>
    </div>

    {{ Form::open(['url' => route('company.whatsapp.settings.store'), 'method' => 'post']) }}
    <div class="card-body px-3">

        <div class="alert alert-light border py-2 mb-3">
            {{ __('Requires a WhatsApp Business Account and app set up in Meta\'s Business Manager first — this screen only stores the number and token Meta gives you once that\'s done.') }}
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="whatsapp_enabled" value="on"
                        id="whatsapp_enabled" {{ $value('whatsapp_enabled') === 'on' ? 'checked' : '' }}>
                    <label class="form-check-label" for="whatsapp_enabled">
                        {{ __('Enable WhatsApp Chat for this business') }}
                    </label>
                </div>
                <small class="text-muted">
                    {{ __('The chat tab is hidden from staff until this is on and the number/token below are both set.') }}
                </small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('WhatsApp business number') }}</label>
                <input type="text" name="whatsapp_business_number" class="form-control"
                    value="{{ $value('whatsapp_business_number') }}" placeholder="+61412345678">
                <small class="text-muted">{{ __('Shown to staff — not used to call the API.') }}</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('Phone number ID') }}</label>
                <input type="text" name="whatsapp_phone_number_id" class="form-control"
                    value="{{ $value('whatsapp_phone_number_id') }}">
                <small class="text-muted">{{ __('From Meta\'s Business Manager, under this number\'s API setup.') }}</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('Access token') }}</label>
                <input type="password" name="whatsapp_meta_access_token" class="form-control" autocomplete="off"
                    placeholder="{{ $tokenSaved ? __('Saved — leave blank to keep it') : __('Paste the long-lived token from Meta') }}">
                <small class="text-muted">
                    {{ $tokenSaved ? __('A token is already saved. Only fill this in to replace it.') : __('Stored encrypted.') }}
                </small>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>
