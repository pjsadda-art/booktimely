@extends('layouts.main')
@section('page-title')
    {{ __('Invoice Pay Types') }}
@endsection
@section('page-breadcrumb')
    {{ __('Invoice Pay Types') }}
@endsection
@section('page-action')
    <div>
        @permission('invoice pay type create')
            <a data-url="{{ route('invoice-pay-type.create') }}"
                data-ajax-popup="true"
                data-title="{{ __('Create Pay Type') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i> {{ __('Create') }}
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Pay Types') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Laratrust::hasPermission('invoice pay type edit') || Laratrust::hasPermission('invoice pay type delete'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pay_types as $pay_type)
                                    <tr>
                                        <td>{{ $pay_type->name }}</td>
                                        <td>{{ $pay_type->description ?? '-' }}</td>
                                        <td>
                                            @if ($pay_type->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        @if (Laratrust::hasPermission('invoice pay type edit') || Laratrust::hasPermission('invoice pay type delete'))
                                            <td class="Action">
                                                <span style="display: flex;">
                                                    @permission('invoice pay type edit')
                                                        <div class="action-btn me-2">
                                                            <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                                data-url="{{ route('invoice-pay-type.edit', $pay_type->id) }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Pay Type') }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('invoice pay type delete')
                                                        <div class="action-btn">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['invoice-pay-type.destroy', $pay_type->id],
                                                                'id' => 'delete-form-' . $pay_type->id,
                                                            ]) !!}
                                                            <a class="mx-3 btn btn-sm align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $pay_type->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endpermission
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
