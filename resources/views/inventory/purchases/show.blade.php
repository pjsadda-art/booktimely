@extends('layouts.main')

@section('page-title'){{ $purchase->purchase_number ?: '#' . $purchase->id }}@endsection
@section('page-breadcrumb')
    {{ __('Purchases') }},{{ $purchase->purchase_number ?: '#' . $purchase->id }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Order') }}</h6>
                @php
                    $badge = ['draft' => 'secondary', 'confirmed' => 'warning', 'received' => 'success'][$purchase->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $badge }}">{{ __(ucfirst($purchase->status)) }}</span>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach ([
                        __('Vendor') => $purchase->vendor->name ?? '-',
                        __('Deliver to') => $purchase->destinationName(),
                        __('Order date') => $purchase->purchase_date ?: '-',
                        __('Expected') => $purchase->expected_date ?: '-',
                        __('Total') => number_format((float) $purchase->total, 2),
                    ] as $label => $value)
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">{{ $label }}</span>
                            <span>{{ $value }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($purchase->remarks)
                    <p class="text-muted small mt-3 mb-0">{{ $purchase->remarks }}</p>
                @endif

                @permission('inventory manage')
                    <div class="mt-3 d-grid gap-2">
                        @if ($purchase->status === 'draft')
                            {{-- Confirming locks the order but moves no stock; that is
                                 the receive step, and keeping them separate is what
                                 allows a partial delivery. --}}
                            <form method="POST" action="{{ route('inventory.purchases.confirm', $purchase->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">{{ __('Confirm order') }}</button>
                            </form>
                            <form method="POST" action="{{ route('inventory.purchases.destroy', $purchase->id) }}"
                                onsubmit="return confirm(@json(__('Delete this draft?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">{{ __('Delete draft') }}</button>
                            </form>
                        @endif
                    </div>
                @endpermission
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Lines') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.purchases.receive', $purchase->id) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-end">{{ __('Ordered') }}</th>
                                    <th class="text-end">{{ __('Received') }}</th>
                                    <th class="text-end">{{ __('Outstanding') }}</th>
                                    <th class="text-end">{{ __('Unit cost') }}</th>
                                    @if ($purchase->status === 'confirmed')
                                        <th style="width:130px;">{{ __('Receive now') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? __('Product') . ' #' . $item->product_id }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.') }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->received_quantity, 4, '.', ''), '0'), '.') }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($item->outstanding(), 4, '.', ''), '0'), '.') }}</td>
                                        <td class="text-end">{{ number_format((float) $item->unit_cost, 2) }}</td>
                                        @if ($purchase->status === 'confirmed')
                                            <td>
                                                <input type="number" step="0.0001" min="0"
                                                    max="{{ $item->outstanding() }}"
                                                    name="received[{{ $item->id }}]" class="form-control form-control-sm"
                                                    placeholder="0" {{ $item->outstanding() <= 0 ? 'disabled' : '' }}>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($purchase->status === 'confirmed')
                        @permission('inventory manage')
                            <p class="text-muted small mt-3">
                                {{ __('Enter what actually arrived. Leave a line blank if none of it came — the order stays open until every line is complete.') }}
                            </p>
                            <button type="submit" class="btn btn-primary">{{ __('Receive goods') }}</button>
                        @endpermission
                    @elseif ($purchase->status === 'draft')
                        <p class="text-muted small mt-3 mb-0">
                            {{ __('Confirm the order before goods can be received against it.') }}
                        </p>
                    @else
                        <p class="text-success small mt-3 mb-0">
                            {{ __('Fully received. Stock has been updated at the destination.') }}
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
