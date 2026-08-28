@extends('layouts.main')
@section('page-title')
    {{ __('Order') }}
@endsection

@section('page-breadcrumb')
    {{ __('Order') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection
