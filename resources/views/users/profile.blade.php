@extends('layouts.main')
@section('page-title')
{{ __('Profile')}}
@endsection
@section('page-breadcrumb')
{{ __('Profile') }}
@endsection
@push('scripts')
    <script>
        "use strict";
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top top-card">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#useradd-1"
                            class="list-group-item list-group-item-action border-0">{{ __('Personal Info') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-2"
                            class="list-group-item list-group-item-action border-0">{{ __('Change Password') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-3"
                            class="list-group-item list-group-item-action border-0">{{ __('Two-Factor Authentication') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        @stack('profile_setting_sidebar')
                    </div>
                </div>
            </div>


            <div class="col-xl-9">
                <div id="useradd-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Personal Information') }}</h5>
                            <small> {{ __('Details about your personal information') }}</small>
                        </div>
                        {{ Form::model($userDetail, ['route' => ['edit.profile'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <div class="form-group">
                                        <img src="{{!empty($userDetail->avatar) ? get_file($userDetail->avatar) : get_file('uploads/users-avatar/avatar.png')}}" id="myAvatar"  alt="user-image" class="img-fluid rounded border-2 border border-primary" width="150px" style="height: 150px">
                                        <div class="choose-files mt-3">
                                            <label for="avatar">
                                                <div class=" bg-primary "> <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                                <input type="file" accept="image/png, image/gif, image/jpeg,  image/jpg"  class="form-control" name="profile" id="avatar" data-filename="avatar-logo">
                                            </label>
                                        </div>
                                        @error('avatar')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <small class="">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.') }}</small>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="form-group">
                                        <label for="name" class="form-label">{{ __('Full Name') }}</label>
                                        <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" id="fullname" placeholder="{{ __('Enter Your Name') }}" value="{{ $userDetail->name }}" required autocomplete="name">
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="form-label">{{ __('Email') }}</label>
                                        <input class="form-control @error('email') is-invalid @enderror" name="email" type="text" id="email" placeholder="{{ __('Enter Your Email Address') }}" value="{{ $userDetail->email }}" required autocomplete="email">
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                         </div>
                        {{ Form::close() }}
                    </div>
                </div>

                <div id="useradd-2">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-2">{{ __('Change Password') }}</h5>
                            <small> {{ __('Details about your account password change') }}</small>
                        </div>
                        {{ Form::model($userDetail, ['route' => ['update.password', $userDetail->id], 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('current_password', __('Current Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('current_password', ['class' => 'form-control','required'=>'required', 'placeholder' => __('Enter Current Password')]) }}
                                        @error('current_password')
                                            <span class="invalid-current_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('new_password', __('New Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('new_password', ['class' => 'form-control','required'=>'required', 'placeholder' => __('Enter New Password')]) }}
                                        @error('new_password')
                                            <span class="invalid-new_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('confirm_password', __('Re-type New Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('confirm_password', ['class' => 'form-control','required'=>'required', 'placeholder' => __('Enter Re-type New Password')]) }}
                                        @error('confirm_password')
                                            <span class="invalid-confirm_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                         </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <div id="useradd-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-2">{{ __('Two-Factor Authentication') }}</h5>
                            <small>{{ __('Add an extra layer of security to your account using an authenticator app.') }}</small>
                        </div>
                        <div class="card-body">
                            @php
                                $mandatory2fa = $userDetail->two_factor_required;
                                if (!$mandatory2fa && $userDetail->type !== 'super admin') {
                                    $userBusiness = \App\Models\Business::find($userDetail->business_id);
                                    $mandatory2fa = $userBusiness && $userBusiness->require_2fa;
                                }
                            @endphp
                            @if($userDetail->two_factor_enabled)
                                <p><span class="badge bg-light-success text-success">&#9679; {{ __('Enabled') }}</span></p>
                                <p class="text-muted">{{ __('Your account is protected with an authenticator app.') }}</p>
                            @else
                                <p><span class="badge bg-light-secondary text-secondary">&#9675; {{ __('Disabled') }}</span></p>
                                <p class="text-muted">{{ __('Add an extra layer of security to your account.') }}</p>
                            @endif

                            @if($mandatory2fa)
                                <p class="text-warning">{{ __('Two-factor authentication is required by your company administrator.') }}</p>
                            @endif

                            <div class="d-flex gap-2 mt-2">
                                @if(!$userDetail->two_factor_enabled)
                                    <a href="{{ route('2fa.setup') }}" class="btn btn-primary">{{ __('Enable 2FA') }}</a>
                                @elseif(!$mandatory2fa)
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">{{ __('Disable 2FA') }}</button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#regenerateRecoveryModal">{{ __('Regenerate Recovery Codes') }}</button>
                                @else
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#regenerateRecoveryModal">{{ __('Regenerate Recovery Codes') }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($userDetail->two_factor_enabled)
                <div class="modal fade" id="disable2faModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('2fa.disable') }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('Disable Two-Factor Authentication') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>{{ __('Are you sure you want to disable two-factor authentication? Your account will no longer require an authenticator code when logging in.') }}</p>
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Current Password') }}</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Authenticator Code') }}</label>
                                        <input type="text" name="code" maxlength="6" inputmode="numeric" class="form-control" placeholder="{{ __('123456') }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-danger">{{ __('Disable 2FA') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="regenerateRecoveryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('2fa.recovery-codes') }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('Regenerate Recovery Codes') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>{{ __('This will invalidate your existing recovery codes and generate a new set.') }}</p>
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Current Password') }}</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Authenticator Code') }}</label>
                                        <input type="text" name="code" maxlength="6" inputmode="numeric" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('Regenerate') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @stack('profile_setting_sidebar_div')
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
    $('#avatar').change(function(){

    let reader = new FileReader();
    reader.onload = (e) => {
        $('#myAvatar').attr('src', e.target.result);
    }
    reader.readAsDataURL(this.files[0]);

    });
</script>
@endpush
