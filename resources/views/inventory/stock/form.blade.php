@extends('layouts.main')

@php
    $titles = [
        'opening' => __('Opening balance'),
        'adjust' => __('Stock adjustment'),
        'transfer' => __('Stock transfer'),
    ];
    $routes = [
        'opening' => route('inventory.stock.opening'),
        'adjust' => route('inventory.stock.adjust'),
        'transfer' => route('inventory.stock.transfer'),
    ];
@endphp

@section('page-title'){{ $titles[$action] }}@endsection
@section('page-breadcrumb')
    {{ __('Stock') }},{{ $titles[$action] }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $routes[$action] }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">{{ __('Product') }}</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">{{ __('Select a product…') }}</option>
                            @foreach ($products as $id => $name)
                                <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($action === 'transfer')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('From') }}</label>
                                <select name="source" class="form-control" required>
                                    <option value="">{{ __('Select…') }}</option>
                                    @foreach ($places as $key => $label)
                                        <option value="{{ $key }}" {{ old('source') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('To') }}</label>
                                <select name="destination" class="form-control" required>
                                    <option value="">{{ __('Select…') }}</option>
                                    @foreach ($places as $key => $label)
                                        <option value="{{ $key }}" {{ old('destination') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="text-muted small">
                            {{ __('Availability at the source is checked before anything moves, and the whole transfer is one ledger entry.') }}
                        </p>
                    @else
                        <div class="mb-3">
                            <label class="form-label">{{ __('Place') }}</label>
                            <select name="place" class="form-control" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($places as $key => $label)
                                    <option value="{{ $key }}" {{ old('place') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($action === 'adjust')
                        <div class="mb-3">
                            <label class="form-label">{{ __('Direction') }}</label>
                            <select name="direction" class="form-control" required>
                                <option value="increase" {{ old('direction') === 'increase' ? 'selected' : '' }}>{{ __('Increase') }}</option>
                                <option value="decrease" {{ old('direction') === 'decrease' ? 'selected' : '' }}>{{ __('Decrease') }}</option>
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Quantity') }}</label>
                        <input type="number" step="0.0001" min="0.0001" name="quantity" class="form-control"
                            value="{{ old('quantity') }}" required>
                    </div>

                    @if ($action === 'adjust')
                        <div class="mb-3">
                            <label class="form-label">{{ __('Reason') }}</label>
                            <textarea name="reason" class="form-control" rows="2" required
                                placeholder="{{ __('e.g. Stocktake correction, breakage, expiry') }}">{{ old('reason') }}</textarea>
                            <small class="text-muted">
                                {{ __('Mandatory — an unexplained adjustment is indistinguishable from a mistake six months later.') }}
                            </small>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">{{ __('Remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.stock.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ $titles[$action] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
