{{ Form::open(['url' => 'customer', 'method' => 'post', 'id' => 'customer-create-form', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
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
        <div class="col-md-6">
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
        <div class="col-md-6">
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

        <div class="col-md-6">
            <div class="form-group">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="communication_sms" name="communication_sms" value="1" disabled>
                    <label class="form-check-label" for="communication_sms">{{ __('SMS Communication') }}</label>
                </div>
                <small class="text-muted d-block">{{ __('Turns on automatically once a valid mobile number is entered.') }}</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="communication_email" name="communication_email" value="1" disabled>
                    <label class="form-check-label" for="communication_email">{{ __('Email Communication') }}</label>
                </div>
                <small class="text-muted d-block">{{ __('Turns on automatically once a valid email is entered.') }}</small>
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
    {{ Form::submit(__('Create'), ['class' => 'btn m-0 btn-primary']) }}
</div>
{{ Form::close() }}

<script>
    // Inline because this view is loaded into the modal by ajax — @@push never
    // reaches the layout. Mirrors Customer::isValidAustralianMobile()/isValidEmail()
    // closely enough to preview the server's own default: a communication toggle
    // turns itself on the moment its matching contact detail looks valid.
    (function () {
        // Scoped to this form, not document.getElementById — the page behind
        // this modal (customer list) has its own filter input with id="email",
        // and a global lookup would silently bind to that one instead.
        var form = document.getElementById('customer-create-form');
        if (!form) { return; }

        var mobileInput = form.querySelector('[name="mobile_no"]');
        var smsToggle = form.querySelector('[name="communication_sms"]');
        var emailInput = form.querySelector('[name="email"]');
        var emailToggle = form.querySelector('[name="communication_email"]');

        function looksLikeAuMobile(raw) {
            var cleaned = (raw || '').replace(/[^0-9+]/g, '');
            if (cleaned.indexOf('00') === 0) { cleaned = '+' + cleaned.slice(2); }
            if (/^61\d{9}$/.test(cleaned)) { cleaned = '+' + cleaned; }
            return /^(?:\+614|04)\d{8}$/.test(cleaned);
        }

        function looksLikeEmail(raw) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((raw || '').trim());
        }

        function sync(input, toggle, check) {
            if (!input || !toggle) { return; }
            var ok = check(input.value);
            toggle.disabled = !ok;
            toggle.checked = ok;
        }

        if (mobileInput) {
            mobileInput.addEventListener('input', function () { sync(mobileInput, smsToggle, looksLikeAuMobile); });
        }
        if (emailInput) {
            emailInput.addEventListener('input', function () { sync(emailInput, emailToggle, looksLikeEmail); });
        }
    })();
</script>
