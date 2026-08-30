{{ Form::open(['route' => 'invoice-pay-type.store', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Pay Type Name')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('pay_type_group_id', __('Group'), ['class' => 'form-label']) }}
            {{ Form::select('pay_type_group_id', $payTypeGroups->pluck('name', 'id'), null, ['class' => 'form-control', 'placeholder' => __('Select Group')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Description')]) }}
        </div>
        <div class="form-group col-md-12">
            <div class="form-check">
                {{ Form::checkbox('is_active', 1, true, ['class' => 'form-check-input', 'id' => 'is_active']) }}
                {{ Form::label('is_active', __('Active'), ['class' => 'form-check-label']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
