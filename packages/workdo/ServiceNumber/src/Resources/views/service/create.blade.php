<div class="col-md-12">
    <div class="form-group">
        {{ Form::label('service_number', __('Service Number'), ['class' => 'form-label']) }}
        {{ Form::text('service_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Service Number'), 'required' => 'required']) }}
        @error('service_number')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
        @enderror
    </div>
</div>