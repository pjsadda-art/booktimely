{{ Form::model($serviceTax, ['route' => ['servicetax.update', $serviceTax->id], 'method' => 'POST', 'class' =>
'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                <div class="d-flex">
                    <div class="input-group mb-0">
                        {{ Form::text('title', $serviceTax->name, ['class' => 'form-control me-2', 'id' => 'name', 'placeholder' =>
                        __('Enter Name'), 'aria-label' => __('Enter Name'), 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('rate', __('Rate (in Percentage)'), ['class' => 'form-label']) }}
                <div class="d-flex">
                    <div class="input-group mb-0">
                        {{ Form::text('rate', $serviceTax->rate, ['class' => 'form-control me-2', 'id' => 'rate', 'placeholder' =>
                        __('Enter Rate'), 'aria-label' => __('Enter Rate'), 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer gap-3">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn m-0 btn-primary']) }}
</div>
{{ Form::close() }}