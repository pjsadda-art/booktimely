    @extends('layouts.auth')
    @section('page-title')
        {{ __('Register') }}
    @endsection
    @section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ Str::upper($lang) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach (languages() as $key => $language)
                    <a href="{{ route('register', $key) }}"
                        class="dropdown-item @if ($lang == $key) text-primary @endif">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
    @endsection
    @php
        $admin_settings = getAdminAllSetting();
    @endphp

    @section('content')
        <div class="card">
            <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate="">
                @csrf
                <div class="card-body">
                    <div class="">
                        <h2 class="mb-3 f-w-600">{{ __('Register') }}</h2>
                    </div>
                    <div class="">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Name">
                            @error('name')
                                <span class="error invalid-name text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Business Name') }}</label>
                            <input id="business_name" type="text" class="form-control @error('business_name') is-invalid @enderror"
                                name="business_name" value="{{ old('business_name') }}" required autocomplete="business_name" placeholder="Business Name">
                            @error('business_name')
                                <span class="error invalid-name text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <input type="hidden" name = "type" value="register" id="type">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required placeholder="E-Mail Address">
                            @error('email')
                                <span class="error invalid-email text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror"
                                name="password" required autocomplete="new-password" placeholder="Password">
                            @error('password')
                                <span class="error invalid-password text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label class="form-label">{{ __('Confirm password') }}</label>
                            <input id="password-confirm" type="password"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password">
                            @error('password_confirmation')
                                <span class="error invalid-password_confirmation text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        @if (module_is_active('GoogleCaptcha') && (isset($admin_settings['google_recaptcha_is_on']) ? $admin_settings['google_recaptcha_is_on'] : 'off') == 'on')
                            @if (isset($admin_settings['google_recaptcha_version']) && $admin_settings['google_recaptcha_version'] == 'v2')
                                <div class="form-group col-lg-12 col-md-12 mt-3">
                                    {!! NoCaptcha::display() !!}
                                    @error('g-recaptcha-response')
                                    <span class="error small text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            @else
                                @if (isset($admin_settings['google_recaptcha_version']) && $admin_settings['google_recaptcha_version'] == 'v3')
                                    <div class="form-group mb-4">
                                        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                                        @error('g-recaptcha-response')
                                            <span class="error small text-danger" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                @endif
                            @endif
                        @endif
                        <div class="d-grid">
                            <button class="btn btn-primary btn-block mt-2" type="submit">{{ __('Register') }}</button>
                        </div>

                    </div>
                    <p class="mb-2 my-4 text-center">{{ __('Already have an account?') }} <a
                            href="{{ route('login', $lang) }}" class="f-w-400 text-primary">{{ __('Login') }}</a></p>
                </div>
            </form>
        </div>
    @endsection
    @push('script')
        @if(module_is_active('GoogleCaptcha') && (isset($admin_settings['google_recaptcha_is_on']) ? $admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
            @if (isset($admin_settings['google_recaptcha_version']) && $admin_settings['google_recaptcha_version'] == 'v2')
            {!! NoCaptcha::renderJs() !!}
            @else
                @if (isset($admin_settings['google_recaptcha_version']) && $admin_settings['google_recaptcha_version'] == 'v3')
                    <script src="https://www.google.com/recaptcha/api.js?render={{ $admin_settings['google_recaptcha_key'] }}"></script>
                    <script>
                        $(document).ready(function() {
                            grecaptcha.ready(function() {
                                grecaptcha.execute('{{ $admin_settings['google_recaptcha_key'] }}', {
                                    action: 'submit'
                                }).then(function(token) {
                                    $('#g-recaptcha-response').val(token);
                                });
                            });
                        });
                    </script>
                @endif
            @endif
        @endif
    @endpush
