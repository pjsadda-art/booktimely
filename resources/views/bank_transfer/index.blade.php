@extends('layouts.main')
@section('page-title')
    {{ __('Bank Transfer Request') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bank Transfer Request') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection
