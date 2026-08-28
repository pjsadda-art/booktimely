@extends('layouts.main')

@section('page-title')
    {{ __('Business') }}
@endsection
@section('page-breadcrumb')
    {{ __('Business') }}
@endsection

@section('page-action')
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <x-datatable :dataTable="$dataTable" />
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.cp_link', function() {
                var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                toastrs('Success', '{{ __('Link copied') }}', 'success')
            });
        });
    </script>
@endpush
