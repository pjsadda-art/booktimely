@extends('layouts.auth')
@section('page-title')
    {{ __('Set Up Two-Factor Authentication') }}
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="mb-3 f-w-600">{{ __('Two-Factor Authentication') }}</h2>

        @if($forced)
            <div class="alert alert-warning">
                {{ __('Your company administrator requires two-factor authentication on your account. Please complete setup to continue.') }}
            </div>
        @endif

        <p class="text-muted">{{ __('Scan this QR code using Google Authenticator, Microsoft Authenticator, Authy, or another compatible authenticator app.') }}</p>

        <div class="text-center my-3">
            {!! $qrCodeSvg !!}
        </div>

        <p class="text-muted mb-1">{{ __('Or enter this key manually:') }}</p>
        <p class="text-center"><code>{{ $secret }}</code></p>

        <form method="POST" action="{{ route('2fa.confirm') }}" class="needs-validation" novalidate>
            @csrf
            <div class="form-group mb-3">
                <label class="form-label">{{ __('Enter the 6-digit code from your app') }}</label>
                <input id="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                    class="form-control text-center @error('code') is-invalid @enderror"
                    name="code" placeholder="{{ __('123456') }}" autofocus autocomplete="one-time-code" required>
                @error('code')
                    <span class="error invalid-code text-danger" role="alert">
                        <small>{{ $message }}</small>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">{{ __('Verify and Enable') }}</button>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">{{ __('Cancel / Logout') }}</button>
        </form>
    </div>
</div>
@endsection
