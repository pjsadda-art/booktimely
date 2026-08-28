@extends('layouts.main')

@section('page-title'){{ __('Stock valuation') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory reports') }},{{ __('Valuation') }}
@endsection

@php $currency = company_setting('defult_currancy_symbol', null, getActiveBusiness()) ?: '$'; @endphp

@section('content')
@include('inventory.reports._nav', ['active' => 'valuation'])

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
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('inventory.reports.valuation') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('Valuation') }}</h6>
        <span class="h5 mb-0">{{ $currency }}{{ number_format($total, 2) }}</span>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            {{ __('Valued at each product\'s current cost price. The ledger records the unit cost of every receipt, so a weighted-average or FIFO valuation can be built on the same data later.') }}
        </p>

        @if (empty($rows))
            <p class="text-muted mb-0">{{ __('Nothing to value.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Place') }}</th>
                            <th class="text-end">{{ __('Quantity') }}</th>
                            <th class="text-end">{{ __('Cost price') }}</th>
                            <th class="text-end">{{ __('Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['product']->name }}</td>
                                <td>{{ $row['place'] }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($row['quantity'], 4, '.', ''), '0'), '.') }}
                                </td>
                                <td class="text-end">{{ $currency }}{{ number_format($row['cost_price'], 2) }}</td>
                                <td class="text-end">{{ $currency }}{{ number_format($row['value'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">{{ __('Total') }}</th>
                            <th class="text-end">{{ $currency }}{{ number_format($total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
