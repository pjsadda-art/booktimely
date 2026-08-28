{{ Form::open(['route' => 'screenshots_store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                {{ Form::text('screenshots_heading', null, ['class' => 'form-control ', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-0">
                {{ Form::label('screenshots', __('Screenshots'), ['class' => 'form-label']) }}
                <input type="file" name="screenshots" class="form-control" required>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer gap-3">
    <input type="button" value="{{ __('Cancel') }}" class="btn m-0 btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn m-0  btn-primary">
</div>
{{ Form::close() }}
