<!DOCTYPE html>
<html lang="en">
@php
    $business = \App\Models\Business::where('slug', request()->route()->parameters['businessSlug'])->first();
    $company_settings = getCompanyAllSetting($business->created_by, $business->id);
    $favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : 'uploads/logo/favicon.png');
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Appointment Tracking') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style-tracking.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="icon"
        href="{{ check_file($favicon) ? get_file($favicon) : get_file('uploads/logo/favicon.png') }}{{ '?' . time() }}"
        type="image/x-icon" />
        <style>
            .card-body .form-label {
                font-weight: bold !important;
            }
        </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
                <h3 class="text-center my-3">{{ __('Appointment Details') }}</h3>
                    <div class="appointment-wrp p-3 px-lg-4">
                        <div class="appointment-header p-3">
                            <h6 class="mb-0">{{ App\Models\Appointment::appointmentNumberWithFormat($appointmentDetails->id, $company_settings) }}</h6>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 mb-0">
                                    <div class="card-body">
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                                <label class="form-label" for="Ddate"
                                                    class="form-label">{{ __('Date: ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                    {{ $appointmentDetails->date ?? ''}}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                            <label class="form-label" for="duration"
                                            class="form-label">{{ __('Duration : ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ $appointmentDetails->time ?? ''}}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                            <label class="form-label" for="service"
                                            class="form-label">{{ __('Service : ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->ServiceData) ? $appointmentDetails->ServiceData->name : '-' }}

                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                                <label class="form-label" for="Ddate"
                                                    class="form-label">{{ __('Staff: ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->StaffData) ? $appointmentDetails->StaffData->name : '-' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                                <label class="form-label" for="Ddate"
                                                    class="form-label">{{ __('Location: ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->LocationData) ? $appointmentDetails->LocationData->name : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 mb-0">
                                    <div class="card-body">
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                            <label class="form-label" for="customer"
                                            class="form-label">{{ __('Customer : ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->CustomerData) ? $appointmentDetails->CustomerData->name : $appointmentDetails->name ?? 'Guest' . ' (Guest)' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                            <label class="form-label" for="contact"
                                            class="form-label">{{ __('Email : ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->CustomerData) ? $appointmentDetails->CustomerData->customer->email : $appointmentDetails->email }}

                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                                <label class="form-label" for="Ddate"
                                                    class="form-label">{{ __('Contact: ') }}</label>
                                            </div>
                                            <div class="content-right">
                                                <span>
                                                {{ !empty($appointmentDetails->CustomerData) ? $appointmentDetails->CustomerData->customer->mobile_no : $appointmentDetails->contact }}
                                                </span>
                                            </div>
                                        </div>

                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-12 mb-4">
                                <div class="card h-100 mb-0">
                                    <div class="card-body">
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                            <label class="form-label" for="amount"
                                            class="form-label">{{ __('Payment : ') }}</label>
                                            </div>
                                            <div class="content-right">
                                            <span>
                                                {{ !empty($appointmentDetails->payment) ? currency_format_with_sym($appointmentDetails->payment->amount, $business['created_by'], $business->id) : 
                                                ((module_is_active('CollaborativeServices')) && !empty($appointmentDetails->payments($appointmentDetails->id)) ? 
                                                currency_format_with_sym($appointmentDetails->payments($appointmentDetails->id)->amount, $business['created_by'], $business->id) : 
                                                currency_format_with_sym(0, $business['created_by'], $business->id)) }}
                                            </span>
                                            </div>
                                        </div>
                                        <div class="card-content d-flex justify-content-between gap-2">
                                            <div class="content-left">
                                                <label class="form-label" for="Ddate"
                                                    class="form-label">{{ __('Amount: ') }}</label>
                                            </div>
                                            <div class="content-right">
                                            <span>
                                                @if (!empty($appointmentDetails->payment))
                                                    {{ currency_format_with_sym($appointmentDetails->payment->amount, $business['created_by'], $business->id) }}
                                                @elseif (module_is_active('CollaborativeServices') && !empty($appointmentDetails->payments($appointmentDetails->id)))
                                                    {{ currency_format_with_sym($appointmentDetails->payments($appointmentDetails->id)->amount, $business['created_by'], $business->id) }}
                                                @else
                                                    {{ currency_format_with_sym(0, $business['created_by'], $business->id) }}
                                                @endif
                                            </span>
                                            </div>
                                        </div>
                                        @if (module_is_active('PromoCodes', $appointmentDetails->created_by))
                                            <div class="card-content d-flex justify-content-between gap-2">
                                                <div class="content-left">
                                                    <label class="form-label" for="Ddate"
                                                        class="form-label">{{ __('Coupon Price: ') }}</label>
                                                </div>
                                                <div class="content-right">
                                                    <span>
                                                        {{ !empty($appointmentDetails->payment) ? currency_format_with_sym($appointmentDetails->payment->coupon_amount, $business['created_by'], $business->id) : currency_format_with_sym(0, $business['created_by'], $business->id) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                        @if (module_is_active('ServiceTax'))
                                            <div class="card-content d-flex justify-content-between gap-2">
                                                <div class="content-left">
                                                    <label class="form-label" for="Ddate" class="form-label">{{ __('Tax Price: ') }}</label>
                                                </div>
                                                <div class="content-right">
                                                    <span>
                                                        {{ !empty($appointmentDetails->payment) ? currency_format_with_sym($appointmentDetails->payment->tax_amount, $business['created_by'], $business->id) : currency_format_with_sym(0, $business['created_by'], $business->id) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        @if (module_is_active('PromoCodes'))
                                            <div class="card-content d-flex justify-content-between gap-2">
                                                <div class="content-left">
                                                    <label class="form-label" for="Ddate" class="form-label">{{ __('Final Price: ') }}</label>
                                                </div>
                                                <div class="content-right">
                                                    <span>
                                                        {{ !empty($appointmentDetails->payment) ? currency_format_with_sym($appointmentDetails->payment->final_amount, $business['created_by'], $business->id) : currency_format_with_sym(0, $business['created_by'], $business->id) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress-bar p-3 px-lg-4">
                        <div class="row">
                                <div class="col-md-12">
                                    <div class="card em-card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ __('Appointment Tracking') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="packege-progress">
                                                @foreach ($allTrackingStatus as $index => $trackingstatus)             
                                                @php
                                                    $icons = App\Models\CustomStatus::icon();
                                                    $icon = $icons[$index] ?? 'ti ti-default-icon';
                                                    $statusColor = $trackingstatus->status_color ? '#' . $trackingstatus->status_color : '#00b4ff';
                                                @endphp

                                                <li class="{{ $trackingstatus->id <= ($appointmentDetails->StatusData->id ?? 0) ? 'active' : '' }}">
                                                    <div class="icon-div">
                                                        <span class="icon-bg " style="border-color: {{ $statusColor }};">
                                                            <i class="{{ $icon }}"></i>
                                                        </span>
                                                    </div>
                                                    <div class="progress-text">
                                                        <p class="m-0" style="background-color: {{ $statusColor }};"><strong>{{ $trackingstatus->title }}</strong></p>
                                                    </div>
                                                </li>

                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
            </div>
    </div>
</body>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/custom-bootstrap.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>