<div class="card" id="recaptcha-sidenav">
    {{ Form::open(['route' => 'recaptcha.setting.store', 'enctype' => 'multipart/form-data']) }}
    @csrf
    <div class="card-header p-3">
        <div class="row">
            <div class="col-sm-10 col-9">
                <h5 class="">{{ __('ReCaptcha Settings') }}</h5>
                <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/" target="_blank" class="text-blue">
                    <small>{{__('How to Get Google reCaptcha Site and Secret key')}}</small>
                </a>
            </div>
            <div class="col-sm-2 col-3 text-end">
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="google_recaptcha_is_on" class="form-check-input input-primary" id="google_recaptcha_is_on"
                        {{ (isset($settings['google_recaptcha_is_on']) && $settings['google_recaptcha_is_on'] == 'on') ? ' checked ' : '' }}>
                    <label class="form-check-label" for="google_recaptcha_is_on"></label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-3 pb-0">
        <div class="col-md-6">
            <div class="form-group col switch-width">
                <label for="google_recaptcha_version" class=" col-form-label pt-0">{{__('Google Recaptcha Version')}}</label>

                <select id="google_recaptcha_version" class="form-control choices" searchEnabled="true" name="google_recaptcha_version">
                    <option value="v2" {{  isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2' ? 'selected' : '' }}>v2</option>
                    <option value="v3" {{ isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v3' ? 'selected' : '' }} >v3</option></select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="google_recaptcha_key" class="form-label">{{ __('Google Recaptcha Key') }}</label>
                    <input class="form-control google_recaptcha" required="required" placeholder="{{ __('Google Recaptcha Key') }}" name="google_recaptcha_key"
                        type="text" value="{{ isset($settings['google_recaptcha_key']) ? $settings['google_recaptcha_key'] : ''  }}"
                        {{ (isset($settings['google_recaptcha_is_on']) && $settings['google_recaptcha_is_on'] == 'on') ? '' : ' disabled' }} id="google_recaptcha_key">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="google_recaptcha_secret" class="form-label">{{ __('Google Recaptcha Secret') }}</label>
                    <input class="form-control google_recaptcha" required="required" placeholder="{{ __('Google Recaptcha Secret') }}"
                        name="google_recaptcha_secret" type="text" value="{{ isset($settings['google_recaptcha_secret']) ? $settings['google_recaptcha_secret'] : '' }}"
                        {{ (isset($settings['google_recaptcha_is_on']) && $settings['google_recaptcha_is_on'] == 'on') ? '' : ' disabled' }} id="google_recaptcha_secret">
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer p-3 text-end">
        <input class="btn btn-print-invoice  btn-primary" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>
    <script>
        "use strict";
        $(document).on('click', '#google_recaptcha_is_on', function() {
            if ($('#google_recaptcha_is_on').prop('checked')) {
                $(".google_recaptcha").removeAttr("disabled");
            } else {
                $('.google_recaptcha').attr("disabled", "disabled");
            }
        });
    </script>
