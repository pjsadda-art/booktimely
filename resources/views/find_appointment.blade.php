@extends('layouts.auth')
@section('page-title')
{{ __('Track Your Appointment') }}
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="">
        <h2 class="mb-3 f-w-600">{{ __('Track Your Appointment') }}</h2>
        </div>
        <form method="POST" action="{{route('track.appointment', $business->slug)}}">
            @csrf
            @if (session()->has('success-alert'))
                <div class="alert alert-success">
                    {{ session()->get('success-alert') }}
                </div>
            @endif
            @if (session()->has('error-alert'))
                <div class="alert alert-danger">
                    {{ session()->get('error-alert') }}
                </div>
            @endif

            <div class="custom-login-form">
                <div class="">
                    <div class="form-group mb-3">
                        <label for="ticket_id" class="form-label">{{ __('Appointment Id') }}</label>
                        <input type="number"
                            class="form-control {{ $errors->has('appointment_number') ? 'is-invalid' : '' }}" min="0"
                            id="appointment_number" name="appointment_number"
                            placeholder="{{ __('Enter Appointment Id') }}" required=""
                            value="{{ old('appointment_number') }}" autofocus>
                        <div class="invalid-feedback d-block">
                            {{ $errors->first('appointment_number') }}
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            id="email" name="email" placeholder="{{ __('Enter Email') }}" reuired=""
                            value="{{ old('email') }}">
                        <div class="invalid-feedback d-block">
                            {{ $errors->first('email') }}
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary btn-submit btn-block mt-2">{{ __('Search') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
