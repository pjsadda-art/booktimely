{{ Form::open(['url' => 'customer-ajax-create', 'method' => 'post', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate', 'id'=>'customerForm']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Customer Name'), 'required' => 'required']) }}
                @error('name')
                    <small class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <div class="form-file">
                    {{Form::label('image',__('Image'),['class'=>'form-label']) }}
                    <input type="file" class="form-control mb-2" name="image" id="image" aria-label="file example" onchange="previewImage(this)">
                    <img src="" class="rounded overflow-hidden mt-2"  id="blah" width="15%" style="display: none;"/>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Customer Email')]) }}
                @error('email')
                    <small class="invalid-email" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                <input type="password" name="password" id="password" class="form-control" placeholder="**********">
                @error('password')
                    <small class="invalid-password" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('mobile_no', __('Mobile No'), ['class' => 'form-label']) }}
                {{ Form::text('mobile_no', null, ['class' => 'form-control', 'placeholder' => __('Enter Customer Mobile No'),'required' => 'required']) }}
                <div class=" text-xs text-danger d-block mt-2">
                    {{ __('Please add mobile number with country code. (ex. +61)') }}
                </div>
                @error('mobile_no')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female'], null, ['class' => 'form-control','required' => 'required']) !!}
                @error('gender')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) }}
                {{ Form::date('dob', null, ['class' => 'form-control', 'placeholder' => __('Select Date')]) }}
                @error('dob')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-0">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => '4']) }}
                @error('description')
                    <small class="invalid-description" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
    </div>
</div>
<div class="modal-footer gap-3">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::button(__('Create'), ['class' => 'btn m-0 btn-primary','id' => 'createCustomerButton']) }}
</div>
{{ Form::close() }}


<script>
$(document).on('click', '#createCustomerButton', function(e) {
    e.preventDefault();

    var $btn = $(this);
    $btn.prop('disabled', true);

    let form = $('#customerForm')[0];
    let formData = new FormData(form);

    $.ajax({
        url: $('#customerForm').attr('action'),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(response) {
            if (response.status) {
                // If the appointment form's customer autocomplete is on the page,
                // push the new customer into it and auto-select it.
                if (response.customer && $('#customer-autocomplete').length) {
                    var c = response.customer;
                    var label = c.text || (c.name + (c.mobile ? ' (' + c.mobile + ')' : ''));
                    $('#customer-autocomplete').val(label);
                    $('#customer-id').val(c.user_id || c.id);
                    $('#customer-autocomplete').trigger('change');
                }

                if (typeof toastrs === 'function') {
                    toastrs('Success', response.message, 'success');
                } else if (typeof show_toastr === 'function') {
                    show_toastr('Success', response.message, 'success');
                }

                $('#commonModalCustomer').modal('hide');
            } else {
                if (typeof toastrs === 'function') {
                    toastrs('Error', response.message, 'error');
                } else {
                    alert(response.message);
                }
                $btn.prop('disabled', false);
            }
        },

        error: function(xhr) {
            if (typeof toastrs === 'function') {
                toastrs('Error', 'Error occurred', 'error');
            } else {
                alert('Error occurred');
            }
            $btn.prop('disabled', false);
        }
    });
});
</script>
