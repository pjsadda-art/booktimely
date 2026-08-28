@extends('layouts.main')

@php $isSample = $kind === 'sample'; @endphp

@section('page-title'){{ $isSample ? __('Samples') : __('Open units') }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory') }},{{ $isSample ? __('Samples') : __('Open units') }}
@endsection

@section('content')
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ !$isSample ? 'active' : '' }}"
            href="{{ route('inventory.internal-use.index', ['kind' => 'open_unit']) }}">{{ __('Open units') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $isSample ? 'active' : '' }}"
            href="{{ route('inventory.internal-use.index', ['kind' => 'sample']) }}">{{ __('Samples') }}</a>
    </li>
</ul>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ $isSample ? __('Record a sample') : __('Open a container') }}</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-light border small">
                    @if ($isSample)
                        {{ __('A sample leaves stock the moment it is handed over. There is no second step.') }}
                    @else
                        {{ __('An opened container leaves sellable stock now, even though it is not empty for weeks. Mark it finished later when it runs out — that moves no stock.') }}
                    @endif
                </div>

                @permission('inventory manage')
                    <form method="POST" action="{{ route('inventory.internal-use.open') }}">
                        @csrf
                        <input type="hidden" name="kind" value="{{ $kind }}">

                        <div class="mb-3">
                            <label class="form-label">{{ __('Product') }}</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($products as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @if (!$isSample)
                                <small class="text-muted">
                                    {{ __('Only products marked as decantable are listed.') }}
                                </small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('From') }}</label>
                            <select name="place" class="form-control" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($places as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quantity') }}</label>
                            <input type="number" step="0.0001" min="0.0001" name="quantity" class="form-control"
                                value="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            {{ $isSample ? __('Record sample') : __('Open container') }}
                        </button>
                    </form>
                @endpermission
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if ($units->isEmpty())
                    <p class="text-muted mb-0">{{ __('Nothing recorded yet.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Place') }}</th>
                                    <th>{{ __('Opened') }}</th>
                                    <th>{{ __('By') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (!$isSample)
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>{{ $unit->product->name ?? __('Product') . ' #' . $unit->product_id }}</td>
                                        <td>{{ $unit->placeName() }}</td>
                                        <td>{{ $unit->opened_at ? $unit->opened_at->format('d M Y') : '-' }}</td>
                                        <td>{{ $unit->openedBy->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $unit->isOpen() ? 'warning' : 'secondary' }}">
                                                {{ $unit->isOpen() ? __('Open') : __('Finished') }}
                                            </span>
                                        </td>
                                        @if (!$isSample)
                                            <td class="text-end">
                                                @if ($unit->isOpen())
                                                    @permission('inventory manage')
                                                        <form method="POST"
                                                            action="{{ route('inventory.internal-use.close', $unit->id) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                {{ __('Mark finished') }}
                                                            </button>
                                                        </form>
                                                    @endpermission
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $units->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
