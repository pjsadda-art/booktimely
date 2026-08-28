@extends('layouts.main')

@section('page-title'){{ __($definition['plural']) }}@endsection
@section('page-breadcrumb')
    {{ __('Inventory') }},{{ __($definition['plural']) }}
@endsection

@php
    // Field labels are derived from the column names in the type definition, so
    // adding a field to the registry adds it to this form with no edit here.
    $labels = [
        'name' => __('Name'),
        'short_name' => __('Short name'),
        'description' => __('Description'),
        'address' => __('Address'),
        'city' => __('City'),
        'postcode' => __('Postcode'),
        'contact_name' => __('Contact name'),
        'email' => __('Email'),
        'phone' => __('Phone'),
    ];
    $parentColumn = $definition['parent_column'] ?? null;
@endphp

@section('content')
<div class="row">
    {{-- Create form --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Add :label', ['label' => __($definition['label'])]) }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.reference.store', $type) }}">
                    @csrf
                    @if ($parentColumn)
                        <div class="mb-3">
                            <label class="form-label">{{ __($definition['parent_label']) }}</label>
                            <select name="{{ $parentColumn }}" class="form-control" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($parents as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @foreach ($definition['fields'] as $field)
                        <div class="mb-3">
                            <label class="form-label">{{ $labels[$field] ?? ucfirst($field) }}</label>
                            @if (in_array($field, ['description', 'address']))
                                <textarea name="{{ $field }}" class="form-control" rows="2"></textarea>
                            @else
                                <input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}"
                                    class="form-control" {{ $field === 'name' ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary w-100">{{ __('Add') }}</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Listing --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if ($rows->isEmpty())
                    <p class="text-muted mb-0">{{ __('Nothing here yet.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    @if ($parentColumn)
                                        <th>{{ __($definition['parent_label']) }}</th>
                                    @endif
                                    @foreach ($definition['fields'] as $field)
                                        <th>{{ $labels[$field] ?? ucfirst($field) }}</th>
                                    @endforeach
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        @if ($parentColumn)
                                            <td>{{ $parents[$row->{$parentColumn}] ?? '-' }}</td>
                                        @endif
                                        @foreach ($definition['fields'] as $field)
                                            <td>{{ $row->{$field} ?: '-' }}</td>
                                        @endforeach
                                        <td class="text-end text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="document.getElementById('edit-{{ $type }}-{{ $row->id }}').classList.toggle('d-none')">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <form method="POST" class="d-inline"
                                                action="{{ route('inventory.reference.destroy', [$type, $row->id]) }}"
                                                onsubmit="return confirm(@json(__('Delete this record?')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    {{-- Inline edit, hidden until the pencil is clicked. --}}
                                    <tr id="edit-{{ $type }}-{{ $row->id }}" class="d-none">
                                        <td colspan="{{ count($definition['fields']) + ($parentColumn ? 2 : 1) }}">
                                            <form method="POST"
                                                action="{{ route('inventory.reference.update', [$type, $row->id]) }}"
                                                class="row g-2 align-items-end">
                                                @csrf
                                                @method('PUT')
                                                @if ($parentColumn)
                                                    <div class="col-md-3">
                                                        <label class="form-label">{{ __($definition['parent_label']) }}</label>
                                                        <select name="{{ $parentColumn }}" class="form-control form-control-sm" required>
                                                            @foreach ($parents as $id => $label)
                                                                <option value="{{ $id }}"
                                                                    {{ $row->{$parentColumn} == $id ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                @foreach ($definition['fields'] as $field)
                                                    <div class="col-md-3">
                                                        <label class="form-label">{{ $labels[$field] ?? ucfirst($field) }}</label>
                                                        <input type="{{ $field === 'email' ? 'email' : 'text' }}"
                                                            name="{{ $field }}" class="form-control form-control-sm"
                                                            value="{{ $row->{$field} }}"
                                                            {{ $field === 'name' ? 'required' : '' }}>
                                                    </div>
                                                @endforeach
                                                <div class="col-md-2">
                                                    <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $rows->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
