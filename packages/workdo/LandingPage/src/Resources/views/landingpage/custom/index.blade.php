@extends('layouts.main')

@section('page-title')
    {{ __('Landing Page') }}
@endsection

@push('scripts')
    <script>
        document.getElementById('site_logo').onchange = function () {
                var src = URL.createObjectURL(this.files[0])
                document.getElementById('image').src = src
            }
    </script>
@endpush

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
            @include('landing-page::landingpage.custom.sitesettings.index')
            @include('landing-page::landingpage.custom.footer.index')
            @include('landing-page::landingpage.custom.custom_js_css.index')
            @include('landing-page::landingpage.custom.google_fonts.index')
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

