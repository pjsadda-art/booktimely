@extends('layouts.main')

@section('page-title')
    {{ __('Landing Page') }}
@endsection

@section('page-breadcrumb')
    {{__('Landing Page')}}
@endsection

@section('page-action')
    <div class="d-flex">
        <a class="btn btn-sm btn-primary btn-icon me-2"
        data-bs-toggle="modal"
        data-bs-target="#qrcodeModal"
        id="download-qr"
        target="_blanks"
        data-bs-placement="top"
        title="{{ __('Qr Code') }}">
            <span class="text-white"><i class="fa fa-qrcode"></i></span>
        </a>
    <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
    data-bs-original-title="{{ __('Preview') }}" href="{{ url('/') }}" target="-blank" ><span
    class="text-white"><i class="ti ti-eye"></i></span></a>
    </div>
@endsection

@section('content')
    <div class="row cms-page-wrp">
        <div class="col-sm-12">
            @include('landing-page::landingpage.sections')
            {{--  Start for all settings tab --}}
            <div class="card">
                {{ Form::model($settings, ['route' => ['landingpage.pwa.setting.save'], 'method' => 'POST', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5>{{ __('PWA') }}</h5>
                        </div>
                        <div id="p1" class="col-auto text-end text-primary">
                            <div class="form-group col-md-4 mb-0">
                                {{-- <label class="form-check-label"
                                    for="is_checkout_login_required"></label> --}}
                                <div class="custom-control form-switch">
                                    <input type="checkbox"
                                        class="form-check-input is_pwa_store_active" name="is_pwa_store_active"
                                        id="pwa_store"
                                        {{ !empty($settings['is_pwa_store_active']) && $settings['is_pwa_store_active'] == 'on' ? 'checked=checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="border rounded overflow-hidden">

                            <div class="p-3 justify-content-center">

                                <div class="row">
                                    <div class="form-group col-md-6 pwa_is_enable">
                                        {{ Form::label('pwa_app_title', __('App Title'), ['class' => 'form-label']) }}
                                        {{ Form::text('pwa_app_title', !empty($pwa->name) ? $pwa->name : '', ['class' => 'form-control', 'placeholder' => __('App Title'),'required' => 'required']) }}
                                    </div>

                                    <div class="form-group col-md-6 pwa_is_enable">
                                        {{ Form::label('pwa_app_name', __('App Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('pwa_app_name', !empty($pwa->short_name) ? $pwa->short_name : '', ['class' => 'form-control', 'placeholder' => __('App Name'),'required' => 'required']) }}
                                    </div>

                                    <div class="form-group input-width col-md-6 pwa_is_enable">
                                        {{ Form::label('pwa_app_background_color', __('App Background Color'), ['class' => 'form-label']) }}
                                        {{ Form::color('pwa_app_background_color', !empty($pwa->background_color) ? $pwa->background_color : '', ['class' => 'form-control color-picker', 'placeholder' => __('18761234567'),'required' => 'required']) }}
                                    </div>

                                    <div class="form-group input-width col-md-6 pwa_is_enable">
                                        {{ Form::label('pwa_app_theme_color', __('App Theme Color'), ['class' => 'form-label']) }}
                                        {{ Form::color('pwa_app_theme_color', !empty($pwa->theme_color) ? $pwa->theme_color : '', ['class' => 'form-control color-picker', 'placeholder' => __('18761234567'),'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                                <div class="card-footer text-end pt-3 p-0">
                                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            {{--  End for all settings tab --}}
        </div>
    </div>
@endsection

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTrigger = document.getElementById('download-qr');
            var tooltip = new bootstrap.Tooltip(tooltipTrigger);
        });
    </script>
@endpush

