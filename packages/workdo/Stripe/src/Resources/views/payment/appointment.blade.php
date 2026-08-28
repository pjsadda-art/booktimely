<div class="col-sm-6 col-lg-4 col-12">
    <div class="radio-group">
        <input class="form-check-input payment_method" name="payment_method" id="stripe-payment" type="radio"
            data-payment="STRIPE" data-payment-action="{{ route('appointment.pay.with.stripe') }}">
        <label for="stripe-payment">
            <div class="radio-img">
                <img src="{{ get_module_img('Stripe') }}" alt="paypal">
            </div>
            <p>{{ Module_Alias_Name('Stripe') }}</p>
        </label>
    </div>
</div>
