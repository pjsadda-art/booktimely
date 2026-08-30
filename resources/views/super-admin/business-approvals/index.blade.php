@extends('layouts.main')

@section('page-title')
    {{ __('Business Approvals') }}
@endsection
@section('page-breadcrumb')
    {{ __('Business Approvals') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header p-3">
                    <h5 class="mb-0">{{ __('Pending Registrations') }}</h5>
                    <small class="text-muted">{{ __('New tenant signups waiting for review.') }}</small>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Business Name') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Industry') }}</th>
                                    <th>{{ __('Registered') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($businesses as $business)
                                    @php
                                        $owner = \App\Models\User::find($business->created_by);
                                    @endphp
                                    <tr>
                                        <td>{{ $business->name }}</td>
                                        <td>{{ $owner->name ?? '-' }}</td>
                                        <td>{{ $owner->email ?? '-' }}</td>
                                        <td>{{ $business->industry->name ?? '-' }}</td>
                                        <td>{{ company_datetime_formate($business->created_at) }}</td>
                                        <td>
                                            <div class="action-btn">
                                                <form action="{{ route('super.admin.business-approvals.approve', $business->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm bg-success-focus text-success-main" title="{{ __('Approve') }}">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('super.admin.business-approvals.reject', $business->id) }}" method="POST" style="display:inline-block;"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to reject this registration?') }}');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm bg-danger-focus text-danger-main" title="{{ __('Reject') }}">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No pending registrations.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
