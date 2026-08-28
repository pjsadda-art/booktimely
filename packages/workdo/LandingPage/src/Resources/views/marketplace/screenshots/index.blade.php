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

                                @include('landing-page::marketplace.tab',[$modules])

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">
                    {{--  Start for all settings tab --}}

                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5>{{ __('Upload Screenshots') }}</h5>
                                </div>
                                <div class="col-auto justify-content-end d-flex">
                                    <a data-size="lg" data-url="{{ route('marketplace_screenshots_create',$slug) }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Screenshots')}}"  class="btn btn-sm btn-primary">
                                        <i class="ti ti-plus text-light"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{__('No')}}</th>
                                            <th>{{__('Name')}}</th>
                                            <th class="text-end">{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       @if (is_array($screenshots) || is_object($screenshots))
                                       @php
                                            $no = 1;
                                            $image_no = 1;
                                        @endphp
                                            @foreach ($screenshots as $key => $value)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $value['screenshots_heading'] }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2 justify-content-end">
                                                            <div class="action-btn">
                                                                    <a href="#" class="btn btn-sm  bg-info  align-items-center" data-url="{{ route('marketplace_screenshots_edit',[$slug , $key, $image_no]) }}" data-ajax-popup="true" data-title="{{__('Edit Screenshots')}}" data-size="lg" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>

                                                            <div class="action-btn">
                                                            {!! Form::open(['method' => 'GET', 'route' => ['marketplace_screenshots_delete', [$slug , $key]],'id'=>'delete-form-'.$key]) !!}

                                                                <a href="#" class="btn btn-sm bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{'delete-form-'.$key}}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php $image_no++; @endphp
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {{--  End for all settings tab --}}
                    </div>
                </div>
        </div>
    </div>
@endsection

