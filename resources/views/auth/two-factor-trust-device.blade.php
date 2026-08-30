@extends('layouts.auth')
@section('page-title')
    {{ __('Trust This Device?') }}
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="mb-3 f-w-600">{{ __('Trust This Device?') }}</h2>
        <p class="text-muted">
            {{ __('Skip the verification code on this browser for a while. Only choose this on a device you trust — anyone using this browser will be able to sign in without a code until it expires.') }}
        </p>

        <form method="POST" action="{{ route('2fa.trust-device.store') }}">
            @csrf
            <div class="d-grid gap-2">
                <button type="submit" name="duration" value="1_day" class="btn btn-outline-primary">{{ __('1 Day') }}</button>
                <button type="submit" name="duration" value="1_week" class="btn btn-outline-primary">{{ __('1 Week') }}</button>
                <button type="submit" name="duration" value="1_month" class="btn btn-outline-primary">{{ __('1 Month') }}</button>
                <button type="submit" name="duration" value="1_qtr" class="btn btn-outline-primary">{{ __('1 Quarter') }}</button>
                <button type="submit" name="duration" value="never" class="btn btn-link text-muted">{{ __('Never — ask every time') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
