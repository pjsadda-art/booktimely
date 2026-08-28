@extends('layouts.main')

@section('page-title')
    {{ __('Promo Code Details') }}
@endsection

@section('page-breadcrumb')
    {{ __('Promo Code Details') }}
@endsection

@section('content')
<div class="row promo-code-table">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Appointment') }}</th>
                                <th style="text-align: center;">{{ __('Discount Amount') }}</th>
                                <th style="text-align: center;">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($promocodes as $promocode)
                                <tr>
                                    <th scope="row">
                                        <a href="#" class="btn btn-sm btn-secondary d-inline align-items-center"
                                            data-url="{{ route('appointment.show', $promocode->appointment_id) }}" data-size="lg"
                                            class="dropdown-item" data-ajax-popup="true"
                                            data-title="{{ __('Appointment Details') }}" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Appointment Details') }}">
                                            <span class="text-white">{{ App\Models\Appointment::appointmentNumberFormat($promocode->appointment_id,$promocode->created_by,$promocode->business_id) }}</span>
                                        </a>
                                    </th>
                                    <td style="text-align: center;">{{ currency_format_with_sym($promocode->coupon_amount) }}</td>
                                    <td style="text-align: center;">{{ company_date_formate($promocode->payment_date) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
