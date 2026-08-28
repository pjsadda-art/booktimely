@extends('layouts.main')

@section('page-title'){{ __('New product') }}@endsection
@section('page-breadcrumb')
    {{ __('Products') }},{{ __('New') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.products.store') }}">
                    @csrf
                    @include('inventory.products._form', ['product' => null])
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Create product') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
