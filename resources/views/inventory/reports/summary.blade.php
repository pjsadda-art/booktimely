@extends('layouts.main')

@section('page-title'){{ __('Stock summary') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory reports') }},{{ __('Stock summary') }}
@endsection

@section('content')
@include('inventory.reports._nav', ['active' => 'summary'])

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Place') }}</label>
                <select name="place" class="form-control">
                    <option value="">{{ __('Everywhere') }}</option>
                    @foreach ($places as $key => $label)
                        <option value="{{ $key }}" {{ request('place') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input type="checkbox" name="include_zero" value="1" class="form-check-input" id="include_zero"
                        {{ request('include_zero') ? 'checked' : '' }}>
                    <label class="form-check-label" for="include_zero">{{ __('Include zero balances') }}</label>
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('inventory.reports.summary') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($rows->isEmpty())
            <p class="text-muted mb-0">{{ __('No stock to report.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('SKU') }}</th>
                            <th>{{ __('Place') }}</th>
                            <th class="text-end">{{ __('Quantity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row->product->name ?? __('Product') . ' #' . $row->product_id }}</td>
                                <td><small class="text-muted">{{ $row->product->sku ?? '-' }}</small></td>
                                <td>{{ $row->placeName() }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format((float) $row->quantity, 4, '.', ''), '0'), '.') ?: '0' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
