@extends('layouts.main')

@section('page-title'){{ __('Purchases') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory') }},{{ __('Purchases') }}
@endsection
@section('page-action')
    @permission('inventory manage')
        <a href="{{ route('inventory.purchases.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('New purchase') }}
        </a>
    @endpermission
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <a href="{{ route('inventory.purchases.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($purchases->isEmpty())
            <p class="text-muted mb-0">{{ __('No purchases yet.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Number') }}</th>
                            <th>{{ __('Vendor') }}</th>
                            <th>{{ __('Destination') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.purchases.show', $purchase->id) }}">
                                        {{ $purchase->purchase_number ?: '#' . $purchase->id }}
                                    </a>
                                </td>
                                <td>{{ $purchase->vendor->name ?? '-' }}</td>
                                <td>{{ $purchase->destinationName() }}</td>
                                <td>{{ $purchase->purchase_date }}</td>
                                <td>
                                    @php
                                        $badge = ['draft' => 'secondary', 'confirmed' => 'warning', 'received' => 'success'][$purchase->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $statuses[$purchase->status] ?? $purchase->status }}</span>
                                </td>
                                <td class="text-end">{{ number_format((float) $purchase->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $purchases->links() }}</div>
        @endif
    </div>
</div>
@endsection
