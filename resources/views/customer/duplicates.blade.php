@extends('layouts.main')

@section('page-title'){{ __('Duplicate customers') }}@endsection
@section('page-breadcrumb')
    {{ __('Customers') }},{{ __('Duplicates') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if (empty($groups))
            <div class="card"><div class="card-body text-center py-5">
                <i class="ti ti-checks" style="font-size:2rem;"></i>
                <h5 class="mt-3 mb-1">{{ __('No likely duplicates found') }}</h5>
                <p class="text-muted mb-0">
                    {{ __('Records are compared by mobile number, email address, then name.') }}
                </p>
            </div></div>
        @else
            <div class="alert alert-info">
                {{ __('Merging re-points every appointment, invoice, message, wallet and loyalty record onto the surviving customer, then deletes the others. It cannot be undone — check the counts before you confirm.') }}
            </div>

            @foreach ($groups as $group)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ $group['reason'] }}</h6>
                            <small class="text-muted">{{ $group['value'] }}</small>
                        </div>
                        <span class="badge bg-secondary">
                            {{ __(':count records', ['count' => count($group['customers'])]) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('customer.merge.form') }}">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-3">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Mobile') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Created') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group['customers'] as $customer)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="ids[]" value="{{ $customer->id }}"
                                                        class="form-check-input" checked>
                                                </td>
                                                <td>{{ $customer->name }}</td>
                                                <td>{{ $customer->customer->mobile_no ?? '-' }}</td>
                                                <td>{{ $customer->customer->email ?? '-' }}</td>
                                                <td>{{ $customer->created_at ? $customer->created_at->format('d M Y') : '-' }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('customer.profile', $customer->id) }}"
                                                        class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @permission('customer edit')
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Review merge') }}</button>
                            @endpermission
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
