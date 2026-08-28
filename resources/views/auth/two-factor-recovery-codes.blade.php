@extends('layouts.auth')
@section('page-title')
    {{ __('Recovery Codes') }}
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="mb-3 f-w-600">{{ __('Two-Factor Authentication Enabled') }}</h2>
        <p class="text-muted">{{ __('Store these recovery codes in a safe place. Each code can only be used once to sign in if you lose access to your authenticator app. They will not be shown again.') }}</p>

        <div class="bg-light border rounded p-3 mb-3" id="recovery-codes-text" style="font-family: monospace;">
            @foreach($codes as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary" onclick="copyRecoveryCodes()">{{ __('Copy') }}</button>
            <button type="button" class="btn btn-outline-secondary" onclick="downloadRecoveryCodes()">{{ __('Download') }}</button>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-primary w-100">{{ __('Continue') }}</a>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function recoveryCodesText() {
        return Array.from(document.querySelectorAll('#recovery-codes-text div')).map(d => d.textContent).join('\n');
    }
    function copyRecoveryCodes() {
        navigator.clipboard.writeText(recoveryCodesText());
    }
    function downloadRecoveryCodes() {
        var blob = new Blob([recoveryCodesText()], { type: 'text/plain' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'recovery-codes.txt';
        link.click();
    }
</script>
@endpush
