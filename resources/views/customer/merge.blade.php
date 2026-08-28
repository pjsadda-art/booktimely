@extends('layouts.main')

@section('page-title'){{ __('Merge customers') }}@endsection
@section('page-breadcrumb')
    {{ __('Duplicates') }},{{ __('Merge') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Choose the record to keep') }}</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    {{ __('Everything owned by the other records moves onto the one you keep, and the others are deleted. This cannot be undone.') }}
                </div>

                <form method="POST" action="{{ route('customer.merge') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width:60px;">{{ __('Keep') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th class="text-end">{{ __('Appointments') }}</th>
                                    <th class="text-end">{{ __('Invoices') }}</th>
                                    <th class="text-end">{{ __('Wallet') }}</th>
                                    <th class="text-end">{{ __('Notes') }}</th>
                                    <th class="text-end">{{ __('Messages') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $customer)
                                    <tr>
                                        <td>
                                            <input type="radio" name="primary_id" value="{{ $customer->id }}"
                                                class="form-check-input" {{ $loop->first ? 'checked' : '' }} required>
                                            <input type="hidden" name="ids[]" value="{{ $customer->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $customer->name }}</div>
                                            <small class="text-muted">
                                                {{ $customer->customer->mobile_no ?? '-' }}
                                                · {{ $customer->customer->email ?? '-' }}
                                            </small>
                                            @if ($customer->is_high_risk)
                                                <span class="badge bg-danger ms-1">{{ __('High risk') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $counts[$customer->id]['appointments'] }}</td>
                                        <td class="text-end">{{ $counts[$customer->id]['invoices'] }}</td>
                                        <td class="text-end">{{ $counts[$customer->id]['wallet'] }}</td>
                                        <td class="text-end">{{ $counts[$customer->id]['notes'] }}</td>
                                        <td class="text-end">{{ $counts[$customer->id]['messages'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted small">
                        {{ __('A high-risk flag on any record is kept. Loyalty points are added together, and missing contact details are filled in from the records being merged away.') }}
                    </p>

                    <div class="d-flex gap-2">
                        <a href="{{ route('customer.duplicates') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger">{{ __('Merge records') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
