@extends('layouts.main')

@section('page-title')
    {{ __('Landing Page') }}
@endsection

@section('page-breadcrumb')
    {{ __('Landing Page') }}
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
            data-bs-original-title="{{ __('Preview') }}" href="{{ url('/') }}" target="-blank"><span
                class="text-white"><i class="ti ti-eye"></i></span></a>
    </div>
@endsection

@section('content')
    <div class="row cms-page-wrp">
        <div class="col-sm-12">
            @include('landing-page::landingpage.sections')
            {{--  Start for all settings tab --}}
            <div class="card">
                <div class="card-header">
                    {{ Form::model(null, ['route' => ['join_us.store'], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('Join User') }}</h5>
                        </div>
                        <div id="p1" class="col-auto text-end text-primary mb-0 h3">
                            <div class="form-check form-switch custom-switch-v1 mb-0">
                                <input type="hidden" name="joinus_status" value="off">
                                <input type="checkbox" class="form-check-input input-primary" name="joinus_status"
                                    id="joinus_status"
                                    {{ !empty($settings['joinus_status']) && $settings['joinus_status'] == 'on' ? 'checked="checked"' : '' }}>
                                <label class="form-check-label" for="customswitchv1-1"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4 border rounded overflow-hidden">
                        <div class="p-3 border-bottom accordion-header">
                            <div class="row align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-9">
                                    <h5 class="mb-0">{{ __('Main') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                        {{ Form::text('joinus_heading', $settings['joinus_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'required' => 'required']) }}
                                        @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::text('joinus_description', $settings['joinus_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'required' => 'required']) }}
                                        @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>
                    @include('landing-page::landingpage.newsletter.join_user.index')
                </div>
            </div>
            {{--  End for all settings tab --}}
        </div>
    </div>
@endsection

@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
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
