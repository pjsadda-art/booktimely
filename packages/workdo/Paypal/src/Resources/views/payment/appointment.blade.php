<div class="col-sm-6 col-lg-4 col-12">
    <div class="radio-group">
        <input class="form-check-input payment_method" name="payment_method" id="Paypal-payment" type="radio"
            data-payment="PAYPAL" data-payment-action="{{ route('appointment.pay.with.paypal') }}">
        <label for="Paypal-payment">
            <div class="radio-img">
                <img src="{{ get_module_img('Paypal') }}" alt="paypal">
            </div>
            <p>{{ Module_Alias_Name('Paypal') }}</p>
        </label>
    </div>
</div>
