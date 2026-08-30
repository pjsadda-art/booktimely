@extends('layouts.main')

@section('page-title')
    {{ __('Invoice Pay Type Groups') }}
@endsection
@section('page-breadcrumb')
    {{ __('Invoice Pay Type Groups') }}
@endsection

@section('page-action')
    <div>
        <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPayTypeGroupModal"
            title="{{ __('Add Group') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header p-3">
                    <form method="GET" action="{{ route('super.admin.invoice-pay-type-groups.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="{{ __('Search by name') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">{{ __('Filter') }}</button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('System') }}</th>
                                    <th>{{ __('Pay Types Using') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payTypeGroups as $group)
                                    <tr>
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->description }}</td>
                                        <td>
                                            @if($group->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->is_system_default)
                                                <span class="badge bg-info" data-bs-toggle="tooltip" title="{{ __('Protected — cannot be renamed, deactivated, or deleted.') }}">{{ __('Protected') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $group->pay_types_count }}</span>
                                        </td>
                                        <td>
                                            <div class="action-btn">
                                                <a href="javascript:void(0);" class="btn btn-sm bg-info-focus text-info-main"
                                                   data-bs-toggle="modal" data-bs-target="#editPayTypeGroupModal{{ $group->id }}">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                                @if(!$group->is_system_default)
                                                    <form action="{{ route('super.admin.invoice-pay-type-groups.toggle', $group->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm bg-warning-focus text-warning-main" title="{{ __('Activate/Deactivate') }}">
                                                            <i class="ti ti-power"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('super.admin.invoice-pay-type-groups.destroy', $group->id) }}" method="POST" style="display:inline-block;"
                                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this pay type group?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm bg-danger-focus text-danger-main">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <!-- Edit modal -->
                                            <div class="modal fade" id="editPayTypeGroupModal{{ $group->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('super.admin.invoice-pay-type-groups.update', $group->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ __('Edit Pay Type Group') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @if($group->is_system_default)
                                                                    <div class="alert alert-info py-2 px-3 mb-3" style="white-space: normal; word-break: break-word; overflow-wrap: break-word;">
                                                                        <i class="ti ti-lock"></i>
                                                                        {{ __('This is a protected system group — its name and active status are locked. Only the description can be edited.') }}
                                                                    </div>
                                                                @endif
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">{{ __('Name') }}</label>
                                                                    @if($group->is_system_default)
                                                                        <input type="text" class="form-control" value="{{ $group->name }}" disabled>
                                                                        <input type="hidden" name="name" value="{{ $group->name }}">
                                                                    @else
                                                                        <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
                                                                    @endif
                                                                </div>
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">{{ __('Description') }}</label>
                                                                    <textarea name="description" class="form-control">{{ $group->description }}</textarea>
                                                                </div>
                                                                @if($group->is_system_default)
                                                                    <div class="form-group mb-0">
                                                                        <label class="form-label d-block">{{ __('Status') }}</label>
                                                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                                                        <small class="text-muted d-block mt-1">{{ __('Protected groups are always active.') }}</small>
                                                                    </div>
                                                                @else
                                                                    <div class="form-group mb-0 form-check">
                                                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active{{ $group->id }}" value="1" {{ $group->is_active ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="is_active{{ $group->id }}">{{ __('Active') }}</label>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No pay type groups found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create modal -->
    <div class="modal fade" id="createPayTypeGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('super.admin.invoice-pay-type-groups.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Pay Type Group') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group mb-0 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="create_is_active" value="1" checked>
                            <label class="form-check-label" for="create_is_active">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
