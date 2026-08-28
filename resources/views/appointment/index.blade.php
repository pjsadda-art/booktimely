@extends('layouts.main')

@section('page-title')
    {{ __('Appointments') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointments') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
    $deposit_module_active = module_is_active('EasyDepositPayments');
@endphp
@section('page-action')
    <div class="d-flex col-auto gap-2">
    @stack('addButtonHook')
        @if (module_is_active('ImportExport'))
            @permission('appointment export')
                @include('import-export::export.button', ['module' => 'appointment'])
            @endpermission
        @endif
        @permission('appointment create')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg"
                data-title="{{ __('Create New Appointment') }}" data-url="{{ route('appointment.create') }}"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"><i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}">
@endpush
@if (module_is_active('OutlookCalendar'))
    @push('css')
        <link rel="stylesheet" href="{{ asset('packages/workdo/OutlookCalendar/src/Resources/assets/custom.css') }}">
    @endpush
@endif
@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-end row-gaps">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {!! Form::label('date', __('Date'), ['class' => 'form-label']) !!}
                                    {!! Form::date('date', $date ?? null, ['class' => 'form-control', 'required' => true]) !!}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {!! Form::label('service', __('Service'), ['class' => 'form-label']) !!}
                                    {!! Form::select('service', $service ?? null, '', ['class' => 'form-control', 'required' => true]) !!}
                                </div>
                            </div>
                            <div class="col-lg-auto col-md-12 col-12  mt-lg-4 mt-1">
                                <div class="row header-btn-wrp">
                                    <div class="col-auto">
                                        <div class="d-flex">
                                        <a class="btn btn-sm btn-primary  me-2" data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}" id="applyfilter"
                                            data-original-title="{{ __('Apply') }}">
                                            <span class="btn-inner--icon d-flex align-items-center justify-center"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="#!" class="btn btn-sm btn-danger reset" data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}" id="clearfilter"
                                            data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon d-flex align-items-center justify-center"><i class="ti ti-refresh text-white-off "></i></span>
                                        </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script src="{{ asset('js/jquery.js') }}"></script> --}}
    <script src="{{ asset('assets/js/bootstrap-datepicker.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '#sendDataButton a', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    beforeSend: function() {
                        $(".loader-wrapper").removeClass('d-none');
                    },
                    success: function(response) {
                        var appointment = response.data;
                        $(".loader-wrapper").addClass('d-none');
                        toastrs('Success', response.message, 'success');
                        location.reload();
                    },
                    error: function(xhr) {
                        $(".loader-wrapper").addClass('d-none');
                        toastrs('Error', xhr.responseJSON.error, 'error');
                    }
                });
            });
        });
    </script>
@endpush
