{{Form::model($business,array('route' => array('business.update', $business->id), 'method' => 'PUT', 'id' => 'business-edit-form')) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Business Name')]) }}
    </div>
    <div class="form-group">
        {{ Form::label('slug', __('Slug'), ['class' => 'col-form-label']) }}
        {{ Form::text('slug', null, ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Business Slug')]) }}
        <span id="slug-msg"></span>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}
@push('scripts')
<script>
    "use strict";
    $(document).on('submit', '#business-edit-form', function(e) {
    e.preventDefault();
    var slug = $('#slug').val();
    $.ajax({
        url: '{{ route('business.check') }}',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
            "business": "{{ $business->id }}",
            "slug": slug,
        },
        beforeSend: function () {
            $(".loader-wrapper").removeClass('d-none');
        },
        success: function(data) {
            $('#slug-msg').empty();
            if (data.success) {
                $('#business-edit-form').unbind('submit').submit();
            } else {
                $('#slug-msg').addClass('text-danger').text(data.error);
            }
        }
    });
});
</script>
@endpush
