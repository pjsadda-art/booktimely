@php
    // Same rule the Customer Profile page's Communication Group uses — a
    // toggle can only be turned on if the matching contact detail is valid.
    $smsToggleDisabled = !\App\Models\Customer::isValidAustralianMobile($customer->customer->mobile_no ?? null);
    $emailToggleDisabled = !\App\Models\Customer::isValidEmail($customer->customer->email ?? null);
@endphp
{{Form::model($customer,array('route' => array('customer.update', $customer->id), 'method' => 'PUT', 'id' => 'business-edit-form','enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                    {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Name'),'required'=>'required'))}}
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
                        <img class="rounded overflow-hidden mt-2"  src="{{check_file($customer->customer->avatar) ? get_file($customer->customer->avatar): get_file('uploads/default/avatar.png')}}" id="blah" width="15%"/>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                    {{Form::text('email',$customer->customer->email,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    @error('email')
                    <small class="invalid-email" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('password',__('Password'),['class'=>'form-label'])}}
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
                    {{Form::label('mobile_no',__('Mobile No'),['class'=>'form-label'])}}
                    {{Form::text('mobile_no',$customer->customer->mobile_no,array('class'=>'form-control','placeholder'=>__('Enter Customer Mobile No')))}}
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
            <div class="col-md-6">
                <div class="form-group">
                    {{Form::label('gender',__('Gender'),['class'=>'form-label'])}}
                    {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female'], null, ['class' => 'form-control']) !!}
                    @error('gender')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{Form::label('dob',__('Date of Birth'),['class'=>'form-label'])}}
                    {{Form::date('dob',null,array('class'=>'form-control','placeholder'=>__('Select Date')))}}
                    @error('dob')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="communication_sms" name="communication_sms"
                            value="1"
                            {{ $customer->communication_sms && !$smsToggleDisabled ? 'checked' : '' }}
                            {{ $smsToggleDisabled ? 'disabled' : '' }}>
                        <label class="form-check-label" for="communication_sms">{{ __('SMS Communication') }}</label>
                        @if ($smsToggleDisabled)
                            <small class="text-danger d-block">{{ __('Requires a valid Australian mobile number (04xxxxxxxx or +614xxxxxxxx).') }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="communication_email" name="communication_email"
                            value="1"
                            {{ $customer->communication_email && !$emailToggleDisabled ? 'checked' : '' }}
                            {{ $emailToggleDisabled ? 'disabled' : '' }}>
                        <label class="form-check-label" for="communication_email">{{ __('Email Communication') }}</label>
                        @if ($emailToggleDisabled)
                            <small class="text-danger d-block">{{ __('Requires a valid email address on file.') }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group mb-0">
                    {{Form::label('description',__('Description'),['class'=>'form-label']) }}
                    {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Description'),'rows' => '4'))}}
                    @error('description')
                    <small class="invalid-description" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group mb-0">
                    <div class="form-check form-switch">
                        {{Form::checkbox('is_walkin', 1, $customer->is_walkin, ['class' => 'form-check-input', 'id' => 'is_walkin'])}}
                        {{Form::label('is_walkin', __('Walk-in Customer'), ['class' => 'form-check-label'])}}
                    </div>
                    <small class="text-muted d-block">{{ __('No contact details required. This customer will receive no SMS or email communication.') }}</small>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer gap-3">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn m-0 btn-primary'))}}
    </div>
    {{Form::close()}}
