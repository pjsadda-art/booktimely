@extends('layouts.main')
@push('scripts')
    <script>
        "use strict";
        $(document).on('click', '.code', function() {
            var type = $(this).val();
            if (type == 'manual') {
                $('#manual').removeClass('d-none');
                $('#manual').addClass('d-block');
                $('#auto').removeClass('d-block');
                $('#auto').addClass('d-none');
            } else {
                $('#auto').removeClass('d-none');
                $('#auto').addClass('d-block');
                $('#manual').removeClass('d-block');
                $('#manual').addClass('d-none');
            }
        });

        $(document).on('click', '#code-generate', function() {
            var length = 10;
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            $('#auto-code').val(result);
        });
    </script>
@endpush
@section('page-title')
    {{ __('Manage Coupon') }}
@endsection
@section('page-breadcrumb')
    {{ __('Coupon') }}
@endsection


@section('page-action')
    <div>
        @permission('coupon create')
            <a href="#" data-size="md" data-url="{{ route('coupons.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                title="{{ __('Create') }}" data-title="{{ __('Create New Coupon') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection
