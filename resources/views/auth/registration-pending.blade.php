@extends('layouts.auth')
@section('page-title')
    {{ __('Registration Submitted') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body text-center p-4">
            <i class="ti ti-clock-hour-4" style="font-size: 3rem;"></i>
            <h2 class="mt-3 f-w-600">{{ __('Registration Submitted') }}</h2>
            <p class="text-muted mt-2">
                {{ __('Thanks for signing up! Your account is pending review by our team. You will be able to log in once it has been approved, and we will notify you by email.') }}
            </p>
            <a href="{{ route('login') }}" class="btn btn-primary mt-3">{{ __('Back to Login') }}</a>
        </div>
    </div>
@endsection
