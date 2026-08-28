@extends('layouts.main')

@section('page-title'){{ __('Products') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory') }},{{ __('Products') }}
@endsection
@section('page-action')
    @permission('inventory manage')
        <a href="{{ route('inventory.products.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('New product') }}
        </a>
    @endpermission
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Search') }}</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                    placeholder="{{ __('Name or SKU') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Category') }}</label>
                <select name="category_id" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Brand') }}</label>
                <select name="brand_id" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($brands as $id => $name)
                        <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($products->isEmpty())
            <p class="text-muted mb-0">{{ __('No products yet.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Brand') }}</th>
                            <th class="text-end">{{ __('In stock') }}</th>
                            <th class="text-end">{{ __('Cost') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php $held = (float) ($balances[$product->id] ?? 0); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.products.show', $product->id) }}">{{ $product->name }}</a>
                                    @if (!$product->is_active)
                                        <span class="badge bg-secondary ms-1">{{ __('Inactive') }}</span>
                                    @endif
                                    @if ($product->sku)
                                        <br><small class="text-muted">{{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ $product->brand->name ?? '-' }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($held, 4, '.', ''), '0'), '.') ?: '0' }}
                                    {{-- Reorder level is a per-product warning, not a hard stop. --}}
                                    @if ((float) $product->reorder_level > 0 && $held <= (float) $product->reorder_level)
                                        <span class="badge bg-warning ms-1">{{ __('Low') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float) $product->cost_price, 2) }}</td>
                                <td class="text-end text-nowrap">
                                    @permission('inventory manage')
                                        <a href="{{ route('inventory.products.edit', $product->id) }}"
                                            class="btn btn-sm btn-outline-secondary"><i class="ti ti-pencil"></i></a>
                                        {{-- A product with movement history is deactivated rather
                                             than deleted, so the ledger stays explicable. --}}
                                        <form method="POST" class="d-inline"
                                            action="{{ route('inventory.products.destroy', $product->id) }}"
                                            onsubmit="return confirm(@json(__('Remove this product? If it has stock movements it will be deactivated instead, so its history is kept.')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endpermission
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
