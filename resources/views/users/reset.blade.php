{{Form::model($user,array('route' => array('user.password.update', $user->id), 'method' => 'post','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('password', __('Password'),['class'=>'form-label']) }}
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Password')}}">
            @error('password')
            <span class="invalid-feedback" role="alert">
               <strong>{{ $message }}</strong>
           </span>
            @enderror
        </div>
        <div class="form-group mb-0">
            {{ Form::label('password_confirmation', __('Confirm Password'),['class'=>'form-label']) }}
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm Password')}}">
        </div>
    </div>
</div>
<div class="modal-footer gap-3">
    <input type="button" value="{{__('Cancel')}}" class="btn m-0 btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn m-0 btn-primary">
</div>
{{Form::close()}}

