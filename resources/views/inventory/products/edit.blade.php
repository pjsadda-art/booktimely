@extends('layouts.main')

@section('page-title'){{ $product->name }}@endsection
@section('page-breadcrumb')
    {{ __('Products') }},{{ __('Edit') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.products.update', $product->id) }}">
                    @csrf
                    @method('PUT')
                    @include('inventory.products._form', ['product' => $product])
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
