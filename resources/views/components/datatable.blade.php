@props(['dataTable'])

@push('css')
    @include('layouts.includes.datatable-css')
@endpush

<div class="card">
    <div class="card-header card-body table-border-style">
        <div class="table-responsive booking-data-table">
            {{ $dataTable->table(['width' => '100%']) }}
        </div>
    </div>
</div>

@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
