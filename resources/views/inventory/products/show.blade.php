@extends('layouts.main')

@section('page-title'){{ $product->name }}@endsection
@section('page-breadcrumb')
    {{ __('Products') }},{{ str_replace(',', ' ', $product->name) }}
@endsection
@section('page-action')
    @permission('inventory manage')
        <a href="{{ route('inventory.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-pencil"></i> {{ __('Edit') }}
        </a>
    @endpermission
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Held stock') }}</h6></div>
            <div class="card-body">
                @if ($stocks->isEmpty())
                    <p class="text-muted mb-0">{{ __('No stock recorded anywhere.') }}</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($stocks as $stock)
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span>{{ $stock->placeName() }}</span>
                                <strong>{{ rtrim(rtrim(number_format((float) $stock->quantity, 4, '.', ''), '0'), '.') ?: '0' }}</strong>
                            </li>
                        @endforeach
                        <li class="d-flex justify-content-between py-2">
                            <strong>{{ __('Total') }}</strong>
                            <strong>{{ rtrim(rtrim(number_format($product->totalQuantity(), 4, '.', ''), '0'), '.') ?: '0' }}</strong>
                        </li>
                    </ul>
                @endif

                @permission('inventory manage')
                    <form method="POST" action="{{ route('inventory.products.rebuild', $product->id) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                            {{ __('Rebuild balances from ledger') }}
                        </button>
                        <small class="text-muted d-block mt-1">
                            {{ __('The balances above are a cache. If a number looks wrong, this recomputes it from the movements below.') }}
                        </small>
                    </form>
                @endpermission
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Details') }}</h6></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach ([
                        __('SKU') => $product->sku ?: '-',
                        __('Category') => $product->category->name ?? '-',
                        __('Brand') => $product->brand->name ?? '-',
                        __('Unit') => $product->unit->name ?? '-',
                        __('Cost price') => number_format((float) $product->cost_price, 2),
                        __('Sale price') => number_format((float) $product->sale_price, 2),
                        __('Reorder level') => rtrim(rtrim(number_format((float) $product->reorder_level, 4, '.', ''), '0'), '.') ?: '0',
                    ] as $label => $value)
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">{{ $label }}</span>
                            <span>{{ $value }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Movement history') }}</h6></div>
            <div class="card-body">
                @if ($transactions->isEmpty())
                    <p class="text-muted mb-0">{{ __('No movements yet.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('When') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('From') }}</th>
                                    <th>{{ __('To') }}</th>
                                    <th class="text-end">{{ __('Qty') }}</th>
                                    <th>{{ __('By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $transaction->created_at ? $transaction->created_at->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td>{{ __($transaction->typeLabel()) }}</td>
                                        <td>{{ $transaction->source_entity_type ? $transaction->sourceName() : '—' }}</td>
                                        <td>{{ $transaction->destination_entity_type ? $transaction->destinationName() : '—' }}</td>
                                        <td class="text-end">
                                            {{ rtrim(rtrim(number_format((float) $transaction->quantity, 4, '.', ''), '0'), '.') }}
                                        </td>
                                        <td>{{ $transaction->performer->name ?? '-' }}</td>
                                    </tr>
                                    @if ($transaction->remarks)
                                        <tr>
                                            <td colspan="6" class="pt-0 pb-2">
                                                <small class="text-muted">{{ $transaction->remarks }}</small>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
