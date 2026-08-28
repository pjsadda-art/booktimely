<div class="form-group">
    {{ Form::label('service_tax', __('Service Tax'), ['class' => 'form-label']) }}
    {{ Form::select('service_tax', $service_tax, null, ['class' => 'form-control', 'required' => 'required']) }}
    @error('service_tax')
        <small class="invalid-service_tax" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </small>
    @enderror
</div>