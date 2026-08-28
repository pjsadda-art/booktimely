@extends('layouts.main')

@section('page-title')
    {{ __('Manage Service Taxes') }}
@endsection

@section('page-breadcrumb')
    {{ __('Service Taxes') }}
@endsection

@section('page-action')
    <div>
        @permission('servicetax create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create New Service Tax') }}"
               data-url="{{ route('servicetax.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
               <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection
