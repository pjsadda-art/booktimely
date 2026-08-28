@php
    $admin_setting = getAdminAllSetting();
@endphp
<div class="form-group col-lg-12 col-md-12 mt-3">
    {!! NoCaptcha::display((!empty($admin_setting['cust_darklayout'] && $admin_setting['cust_darklayout'] == 'on') ? ['data-theme' => 'dark'] : [])) !!}
    @error('g-recaptcha-response')
        <span class="error small text-danger" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
