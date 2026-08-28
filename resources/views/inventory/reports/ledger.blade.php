@extends('layouts.main')

@section('page-title'){{ __('Stock ledger') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory reports') }},{{ __('Stock ledger') }}
@endsection

@section('content')
@include('inventory.reports._nav', ['active' => 'ledger'])

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Product') }}</label>
                <select name="product_id" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($products as $id => $name)
                        <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Type') }}</label>
                <select name="type" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Place') }}</label>
                <select name="place" class="form-control">
                    <option value="">{{ __('Everywhere') }}</option>
                    @foreach ($places as $key => $label)
                        <option value="{{ $key }}" {{ request('place') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-md-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('inventory.reports.ledger') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($transactions->isEmpty())
            <p class="text-muted mb-0">{{ __('No movements match those filters.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('From') }}</th>
                            <th>{{ __('To') }}</th>
                            <th class="text-end">{{ __('Qty') }}</th>
                            <th>{{ __('By') }}</th>
                            <th>{{ __('Remarks') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="text-nowrap">
                                    {{ $transaction->created_at ? $transaction->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td>{{ $transaction->product->name ?? '#' . $transaction->product_id }}</td>
                                <td>{{ __($transaction->typeLabel()) }}</td>
                                {{-- A transfer carries both ends on one row, so nothing
                                     has to be matched up to read this. --}}
                                <td>{{ $transaction->source_entity_type ? $transaction->sourceName() : '—' }}</td>
                                <td>{{ $transaction->destination_entity_type ? $transaction->destinationName() : '—' }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format((float) $transaction->quantity, 4, '.', ''), '0'), '.') }}
                                </td>
                                <td>{{ $transaction->performer->name ?? '-' }}</td>
                                <td><small class="text-muted">{{ $transaction->remarks }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
