@extends('layouts.auth')
@section('page-title')
    {{ __('Two-Factor Authentication') }}
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="mb-3 f-w-600">{{ __('Two-Factor Authentication') }}</h2>
        <p class="text-muted">{{ __('Enter the 6-digit verification code from your authenticator app.') }}</p>

        <form method="POST" action="{{ route('2fa.verify') }}" class="needs-validation" novalidate id="two_fa_form">
            @csrf
            <div class="form-group mb-3">
                <input id="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                    class="form-control text-center @error('code') is-invalid @enderror"
                    name="code" placeholder="{{ __('123456') }}" autofocus autocomplete="one-time-code" required>
                @error('code')
                    <span class="error invalid-code text-danger" role="alert">
                        <small>{{ $message }}</small>
                    </span>
                @enderror
                <small class="text-muted d-block mt-2" id="recovery-toggle" style="cursor:pointer;">
                    {{ __('Use a recovery code instead') }}
                </small>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('two_fa_logout_form').submit()">{{ __('Cancel / Logout') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Verify') }}</button>
            </div>
        </form>
        <form method="POST" action="{{ route('logout') }}" id="two_fa_logout_form" class="d-none">
            @csrf
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('code').focus();
    document.getElementById('recovery-toggle').addEventListener('click', function () {
        var input = document.getElementById('code');
        input.removeAttribute('maxlength');
        input.removeAttribute('pattern');
        input.removeAttribute('inputmode');
        input.placeholder = "{{ __('Recovery code') }}";
        input.value = '';
        input.focus();
    });
</script>
@endpush
