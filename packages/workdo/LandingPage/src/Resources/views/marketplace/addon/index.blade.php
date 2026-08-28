@extends('layouts.main')

@section('page-title')
    {{ __('Marketplace') }}
@endsection

@section('page-breadcrumb')
    {{__('Marketplace')}}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        @include('landing-page::marketplace.modules')
                <div class="row">
                    <div class="col-xl-3">
                        <div class="card sticky-top" style="top:30px">
                            <div class="list-group list-group-flush" id="useradd-sidenav">
                                @include('landing-page::marketplace.tab')
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9">
                    {{--  Start for all settings tab --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{ __('Add On Head details') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::open(array('route' => array('addon_store',$slug), 'method'=>'post', 'enctype' => "multipart/form-data",'class'=>'needs-validation','novalidate')) }}
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                            {{ Form::text('addon_heading',$settings['addon_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required' => 'required']) }}
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('addon_description', $settings['addon_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'addon_description','required'=>'required']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="btn btn-print-invoice btn-primary" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        {{ Form::close() }}
                    </div>
                    {{--  End for all settings tab --}}
                    </div>
                </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
