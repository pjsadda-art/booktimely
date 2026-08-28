@php
    $rtl = Cookie::get('THEME_RTL');
@endphp
<button  class="rtl-btn">
    <div class="form-check form-switch">
        <label class="form-check-label" for="rtlchecked">{{ __('RTL') }}</label>
        <input class="form-check-input rtlswitch" type="checkbox" role="switch" id="rtlchecked" name="rtlchecked" {{ isset($rtl) && $rtl == '1' ? 'checked' : '' }}>
    </div>
</button>