@extends('layouts.main')
@section('page-title')
{{ __('Two-Factor Authentication') }}
@endsection
@section('page-breadcrumb')
{{ __('Company Users Security') }}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Two-Factor Authentication') }}</h5>
                <small>{{ __('View and manage 2FA status for company users.') }}</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('2FA Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                            <tr>
                                <td>{{ $u['name'] }} <br><small class="text-muted">{{ $u['email'] }}</small></td>
                                <td>{{ $u['business'] }}</td>
                                <td>{{ ucfirst($u['role']) }}</td>
                                <td>
                                    @if($u['status'] === 'Enabled')
                                        <span class="badge bg-light-success text-success">{{ __('Enabled') }}</span>
                                    @elseif($u['status'] === 'Required by Admin')
                                        <span class="badge bg-light-warning text-warning">{{ __('Required by Admin') }}</span>
                                    @else
                                        <span class="badge bg-light-secondary text-secondary">{{ __('Disabled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('super.admin.2fa.require', $u['id']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Require') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('super.admin.2fa.reset', $u['id']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('super.admin.2fa.disable', $u['id']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disable') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
