@extends('layouts.main')

@section('page-title'){{ __('Stock') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory') }},{{ __('Stock') }}
@endsection
@section('page-action')
    @permission('inventory manage')
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.stock.form', 'opening') }}" class="btn btn-sm btn-outline-primary">{{ __('Opening balance') }}</a>
            <a href="{{ route('inventory.stock.form', 'adjust') }}" class="btn btn-sm btn-outline-primary">{{ __('Adjust') }}</a>
            <a href="{{ route('inventory.stock.form', 'transfer') }}" class="btn btn-sm btn-primary">{{ __('Transfer') }}</a>
        </div>
    @endpermission
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Search') }}</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                    placeholder="{{ __('Product name or SKU') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Place') }}</label>
                <select name="place" class="form-control">
                    <option value="">{{ __('Everywhere') }}</option>
                    @foreach ($places as $key => $label)
                        <option value="{{ $key }}" {{ request('place') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <a href="{{ route('inventory.stock.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($stocks->isEmpty())
            <p class="text-muted mb-0">{{ __('No stock recorded yet. Start with an opening balance.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Place') }}</th>
                            <th class="text-end">{{ __('Quantity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks as $stock)
                            <tr>
                                <td>
                                    @if ($stock->product)
                                        <a href="{{ route('inventory.products.show', $stock->product_id) }}">
                                            {{ $stock->product->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ __('Product') }} #{{ $stock->product_id }}</span>
                                    @endif
                                </td>
                                <td>{{ $stock->placeName() }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format((float) $stock->quantity, 4, '.', ''), '0'), '.') ?: '0' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
@endsection
