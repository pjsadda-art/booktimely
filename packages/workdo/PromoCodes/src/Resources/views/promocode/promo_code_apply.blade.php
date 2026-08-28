@php
    $company_settings = getCompanyAllSetting($business->created_by);
    $currency_setting = json_encode(
        Arr::only(getCompanyAllSetting($business->created_by), [
            'site_currency_symbol_position',
            'currency_format',
            'currency_space',
            'site_currency_symbol_name',
            'defult_currancy_symbol',
            'defult_currancy',
            'float_number',
            'decimal_separator',
            'thousand_separator',
        ]),
    );
@endphp

{{-- @push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/PromoCodes/src/Resources/assets/css/style.css') }}">
@endpush --}}

<div class="promo_code">
    {{ __('Promo Code') }}
    <div class="promo-code-wrapper">
        <input type="text" name="promocode" id="promocode" class="form-control">
        <input type="hidden" id="after_promo_price" name="after_promo_price" value="">
        <input type="hidden" id="promo_code_id" name="promo_code_id" value="">
        <button class="btn btn-primary" type="button" id="promo_code_apply">{{ __('Apply') }}</button>
    </div>
</div>

@push('script')
    <script>
        $(document).ready(function() {
            canculatePromocode();

            // for bulk appointment module -  execute when promo code & bulk appointment running together.
            $(document).on('change', '#quantitySelect', function() {
                var selectedSloteTime = $("input[name='duration']:checked").val();
                if (selectedSloteTime) {
                    $('.coupon-price-tag').remove();
                    $('.final-price-tag').remove();
                }
            })

            // for team booking module -  execute when promo code & team booking running together.
            $(document).on('change', '#Team', function() {
                var selectedSloteTime = $("input[name='duration']:checked").val();
                if (selectedSloteTime) {
                    $('.coupon-price-tag').remove();
                    $('.final-price-tag').remove();
                }
            })
        });

        function canculatePromocode() {
            $("#promo_code_apply").on('click', function() {
                var serviceValue = $('#serviceSelect').val();
                var promoCode = $('#promocode').val();
                var service_price = $('#final_amount').val();
                // use for flexible hours
                var staffSelect = $('#staffSelect').val();
                var selectedDate = $('#datepicker').val();
                var selectedSloteTime = $("input[name='duration']:checked").val();
                var selectedCartId = $("input[name='selectedCartIds']").val();
                var selectedServiceIds = $("input[name='selectedServiceIds']").val();
                var all_service_price = $("input[name='all_service_price']").val();
                //flexible hours
                var flexible_service_price = $("input[name='flexible_hours']").val();
                var flexible_id = $("input[name='duration']:checked").attr('data-id');
                //Additional Services
                var additional_service_price = $("input[name='additional_service_price']").val();
                var additional_total_price = $("input[name='additional_total_price']").val();
                var quantity = $('#quantitySelect').val(); // For BulkAppointments
                var team = $('#Team').val(); // For TeamBooking
                var formData = new FormData();
                var activeTabContent = $('.tab-content.active');
                var formType = activeTabContent.attr('id');
                var customerTab = activeTabContent.find('input');

                //Repeat Appointment
                var selectedDates = $("input[name='date[]']").map(function() {
                    return $(this).val();
                }).get();
                var bookedSlot = $("input[name='booked_slot[]']").map(function() {
                    return $(this).val();
                }).get();
                var totalRepeatAppointment = $('#total_appointment_count').val();



                customerTab.each(function() {
                    var inputName = $(this).attr('name');
                    var inputValue = $(this).val();
                    formData.append(inputName, inputValue);
                });

                //Sequential Appointment
                var sequential_service = [];
                var service = $('#serviceSelect').val();
                $('[data-repeater-item]').each(function(index, element) {
                    var $repeaterItem = $(element);
                    var serviceValue = $repeaterItem.find('.sequential_service').val();
                    sequential_service.push(serviceValue);
                });
                if (sequential_service.length !== 0) {
                    sequential_service.push(service);
                }
                //End

                // Add additional data to FormData object
                formData.append('service', serviceValue);
                formData.append('promocode', promoCode);
                formData.append('quantity', quantity); // For BulkAppointments
                formData.append('team', team); // For TeamBooking
                formData.append('type', formType);
                formData.append('staffSelect', staffSelect);
                formData.append('selectedDate', selectedDate);
                formData.append('selectedSloteTime', selectedSloteTime);
                formData.append('cart_service_id', selectedServiceIds);
                formData.append('selectedCartId', selectedCartId);
                formData.append('sequentialServices', sequential_service); //Sequential Appointment
                formData.append('flexible_hours', flexible_service_price);
                formData.append('flexible_id', flexible_id);
                formData.append('additional_service_price', additional_service_price); //Additional Services
                formData.append('additional_total_price', additional_total_price); //Additional Services
                formData.append('selectedDates', selectedDates);
                formData.append('bookedSlots', bookedSlot);
                formData.append('totalRepeatAppointment', totalRepeatAppointment);

                if (service_price !== "" && service_price !== undefined) {
                    formData.append('service_price', service_price);
                }
                if (all_service_price !== "" && all_service_price !== undefined) {
                    formData.append('all_service_price', all_service_price);
                }
                var customerTab = customerTab.serialize();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $('#loader').fadeIn();
                $.ajax({
                    url: '{{ route('apply.promocode') }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        console.log(data);
                        $('#loader').fadeOut();
                        var sectionTitleDiv = $(
                            '.appointment-form.payment-method-form .appointment_info_details'
                        );
                        sectionTitleDiv.children('p:not([id])').remove();

                        var promo_code_price = data.apply_discount ?? 0;
                        if (data.hasOwnProperty('error')) {
                            toastrs('error', data.error, 'Error');
                            return false;
                        }
                        if (data.after_promo_tax !== "" && data.after_promo_tax !== undefined) {
                            var tax_data = data.after_promo_tax[0];

                            var service_price = tax_data.service_price;
                            var tax_amount = tax_data.tax_amount;
                            var last_tax_amount = tax_data.tax;
                            var tax_type = tax_data.tax_type;

                            if (tax_type == 'exclude_taxes') {

                                var service = 'Coupon Price: ' + formatCurrency(promo_code_price,
                                    '{{ $currency_setting }}');

                                var serviceTag = $('<p>').text(service).addClass('h6');
                                sectionTitleDiv.append(serviceTag);
                                taxText = 'Service Tax: ' +
                                    formatCurrency(tax_amount,
                                        '{{ $currency_setting }}');


                                var taxPTag = $('<p>').text(taxText).addClass('h6');
                                sectionTitleDiv.append(taxPTag);

                                var final = 'Final Price: ' + formatCurrency(last_tax_amount,
                                    '{{ $currency_setting }}');

                                var finalTag = $('<p>').text(final).addClass('h6');
                                sectionTitleDiv.append(finalTag);

                                var last_amount = $('<input>', {
                                    type: 'hidden',
                                    name: 'service_price',
                                    value: service_price,
                                    id: 'service_price'
                                });
                                var tax = $('<input>', {
                                    type: 'hidden',
                                    name: 'tax_amount',
                                    value: tax_amount,
                                    id: 'tax_amount'
                                });
                                var tax_price = $('<input>', {
                                    type: 'hidden',
                                    name: 'tax_amount_after_promo',
                                    value: tax_amount,
                                    id: 'tax_amount'
                                });
                                sectionTitleDiv.append(last_amount);
                                sectionTitleDiv.append(tax);
                                sectionTitleDiv.append(tax_price);
                                // $('#serviceAmount').html('Service Amount: ' + data.final_amount);

                                // var taxInput = $('<input>', { type: 'hidden', name: 'service_tax', value: tax_amount });
                                $('#after_promo_price').val(last_tax_amount);
                                $('#promo_code_id').val(data.promo_code_id);
                                sectionTitleDiv.append(taxInput);
                            } else {
                                var final = 'Final Price: ' + formatCurrency(last_tax_amount,
                                    '{{ $currency_setting }}');

                                var last_amount = $('<input>', {
                                    type: 'hidden',
                                    name: 'service_price',
                                    value: service_price,
                                    id: 'service_price'
                                });
                                sectionTitleDiv.append(last_amount);

                                $('#serviceAmount').html(final + ' (Total Amount Containg all taxes*)');
                                var taxInput = $('<input>', {
                                    type: 'hidden',
                                    name: 'service_tax',
                                    value: tax_amount
                                });
                                $('#after_promo_price').val(last_tax_amount);
                                $('#promo_code_id').val(data.promo_code_id);
                                sectionTitleDiv.append(taxInput);
                            }
                        } else if (data.hasOwnProperty('after_discount_promo') && data
                            .after_discount_promo != "") {
                            var after_discount_promo = data.after_discount_promo;

                            if (after_discount_promo.promo_discount != null ||
                                after_discount_promo.promo_discount != 0) {
                                var service = 'Coupon Price: ' + formatCurrency(after_discount_promo
                                    .promo_discount, '{{ $currency_setting }}');

                                var serviceTag = $('<p>').text(service).addClass('h6');
                                sectionTitleDiv.append(serviceTag);


                                var taxDiscount = 'Discount Amount: ' + formatCurrency(
                                    after_discount_promo
                                    .service_discount,
                                    '{{ $currency_setting }}');

                                var taxPDiscount = $('<p>').text(taxDiscount).addClass('h6');
                                sectionTitleDiv.append(taxPDiscount);
                                var final_Price = 'Final Price: ' + formatCurrency(after_discount_promo
                                    .service_after_promo, '{{ $currency_setting }}');

                                var finalPrice = $('<p>').text(final_Price).addClass('h6');
                                sectionTitleDiv.append(finalPrice);

                                var coupon_price = $('<input>', {
                                    type: 'hidden',
                                    name: 'coupon_price',
                                    value: after_discount_promo.promo_discount,
                                    id: 'coupon_price'
                                });
                                sectionTitleDiv.append(coupon_price);

                                var after_promo_discount = $('<input>', {
                                    type: 'hidden',
                                    name: 'after_promo_discount',
                                    value: after_discount_promo.service_after_promo,
                                    id: 'after_promo_discount'
                                });
                                sectionTitleDiv.append(after_promo_discount);
                            }
                        } else if (data.hasOwnProperty('cart_discount') && data.cart_discount != "") {
                            var cart_discount = data.cart_discount;
                            var coupon = 'Coupon Price: ' + formatCurrency(cart_discount.promo_discount,
                                '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6');
                            sectionTitleDiv.append(couponTag);
                            var final_Price = 'Final Price: ' + formatCurrency(cart_discount
                                .final_amount, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6');
                            sectionTitleDiv.append(finalPrice);

                            var cart_coupon_price = $('<input>', {
                                type: 'hidden',
                                name: 'cart_coupon_price',
                                value: cart_discount.promo_discount,
                                id: 'cart_coupon_price'
                            });
                            sectionTitleDiv.append(coupon_price);

                            var after_promo_discount = $('<input>', {
                                type: 'hidden',
                                name: 'cart_promo_discount',
                                value: cart_discount.final_amount,
                                id: 'cart_promo_discount'
                            });
                            sectionTitleDiv.append(after_promo_discount);
                        } else if (data.hasOwnProperty('deposit_discount') && data
                            .deposit_discount != "") {
                            var deposit_discount = data.deposit_discount;


                            var deposit_Price = 'Deposit Amount: ' + formatCurrency(deposit_discount
                                .deposit_amount, '{{ $currency_setting }}');

                            var depositPrice = $('<p>').text(deposit_Price).addClass('h6');
                            sectionTitleDiv.append(depositPrice);
                            var coupon = 'Coupon Price: ' + formatCurrency(deposit_discount
                                .promo_discount, '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6');
                            sectionTitleDiv.append(couponTag);

                            var final_Price = 'Final Price: ' + formatCurrency(deposit_discount
                                .service_after_promo, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6');
                            sectionTitleDiv.append(finalPrice);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: deposit_discount.service_after_promo,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);

                            var deposit_price = $('<input>', {
                                type: 'hidden',
                                name: 'deposit_price',
                                value: deposit_discount.deposit_amount,
                                id: 'deposit_price'
                            });
                            sectionTitleDiv.append(deposit_price);
                            var deposit_payment_type = $('<input>', {
                                type: 'hidden',
                                name: 'deposit_payment_type',
                                value: deposit_discount.deposit_payment_setting,
                                id: 'deposit_payment_type'
                            });
                            sectionTitleDiv.append(deposit_payment_type);

                        } else if (data.hasOwnProperty('flexible_price') && data.flexible_price != "") {
                            var flexible_discount = data.flexible_price;
                            var coupon = 'Coupon Price: ' + formatCurrency(flexible_discount
                                .promo_discount, '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6');
                            sectionTitleDiv.append(couponTag);
                            var final_Price = 'Final Price: ' + formatCurrency(flexible_discount
                                .service_after_promo, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6');
                            sectionTitleDiv.append(finalPrice);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: flexible_discount.service_after_promo,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);

                        } else if (data.hasOwnProperty('additional_service') && data
                            .additional_service != "") {
                            var flexible_discount = data.additional_service;
                            var coupon = 'Coupon Price: ' + formatCurrency(flexible_discount
                                .promo_discount, '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6');
                            sectionTitleDiv.append(couponTag);

                            var final_Price = 'Final Price: ' + formatCurrency(flexible_discount
                                .service_after_promo, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6');
                            sectionTitleDiv.append(finalPrice);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: flexible_discount.service_after_promo,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);

                        } else if (data.hasOwnProperty('bulk_discount') && data.bulk_discount !=
                            "") {
                            var bulk_discount = data.bulk_discount;
                            var coupon = 'Coupon Price: ' + formatCurrency(bulk_discount.promo_discount,
                                '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6 coupon-price-tag');
                            sectionTitleDiv.append(couponTag);

                            var final_Price = 'Final Price: ' + formatCurrency(bulk_discount
                                .final_amount, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6 final-price-tag');
                            sectionTitleDiv.append(finalPrice);

                            var bulk_coupon_price = $('<input>', {
                                type: 'hidden',
                                name: 'coupon_price',
                                value: bulk_discount.promo_discount,
                                id: 'coupon_price'
                            });
                            sectionTitleDiv.append(bulk_coupon_price);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: bulk_discount.final_amount,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);
                        } else if (data.hasOwnProperty('team_discount') && data.team_discount != "") {
                            var team_discount = data.team_discount;
                            var coupon = 'Coupon Price: ' + formatCurrency(team_discount.promo_discount,
                                '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6 coupon-price-tag');
                            sectionTitleDiv.append(couponTag);

                            var final_Price = 'Final Price: ' + formatCurrency(team_discount
                                .final_amount, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6 final-price-tag');
                            sectionTitleDiv.append(finalPrice);

                            var team_coupon_price = $('<input>', {
                                type: 'hidden',
                                name: 'coupon_price',
                                value: team_discount.promo_discount,
                                id: 'coupon_price'
                            });
                            sectionTitleDiv.append(team_coupon_price);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: team_discount.final_amount,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);
                        } else if (data.hasOwnProperty('sequential_services') && data
                            .sequential_services != "") {
                            var sequential_services = data.sequential_services;
                            var coupon = 'Coupon Price: ' + formatCurrency(sequential_services
                                .promo_discount, '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6 coupon-price-tag');
                            sectionTitleDiv.append(couponTag);

                            var final_Price = 'Final Price: ' + formatCurrency(sequential_services
                                .final_amount, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6 final-price-tag');
                            sectionTitleDiv.append(finalPrice);

                            var team_coupon_price = $('<input>', {
                                type: 'hidden',
                                name: 'coupon_price',
                                value: sequential_services.promo_discount,
                                id: 'coupon_price'
                            });
                            sectionTitleDiv.append(team_coupon_price);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: sequential_services.final_amount,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);
                        } else if (data.hasOwnProperty('repeat_appointment') && data
                            .repeat_appointment != "") {
                            var repeatAppointmentDiscoumt = data.repeat_appointment;
                            var coupon = 'Coupon Price: ' + formatCurrency(repeatAppointmentDiscoumt
                                .promo_discount, '{{ $currency_setting }}');

                            var couponTag = $('<p>').text(coupon).addClass('h6 coupon-price-tag');
                            sectionTitleDiv.append(couponTag);
                            var final_Price = 'Final Price: ' + formatCurrency(repeatAppointmentDiscoumt
                                .final_amount, '{{ $currency_setting }}');

                            var finalPrice = $('<p>').text(final_Price).addClass('h6 final-price-tag');
                            sectionTitleDiv.append(finalPrice);

                            var team_coupon_price = $('<input>', {
                                type: 'hidden',
                                name: 'coupon_price',
                                value: repeatAppointmentDiscoumt.promo_discount,
                                id: 'coupon_price'
                            });
                            sectionTitleDiv.append(team_coupon_price);

                            var service_after_promo = $('<input>', {
                                type: 'hidden',
                                name: 'service_after_promo',
                                value: repeatAppointmentDiscoumt.final_amount,
                                id: 'service_after_promo'
                            });
                            sectionTitleDiv.append(service_after_promo);
                        } else {
                            var afterPromoPrice = data.final_amount;

                            $('#after_promo_price').val(afterPromoPrice);
                            $('#promo_code_id').val(data.promo_code_id);
                            var last_amount = $('<input>', {
                                type: 'hidden',
                                name: 'last_amount',
                                value: data.final_amount,
                                id: 'last_amount'
                            });

                            if (data.hasOwnProperty('apply_discount')) {
                                var coupon_code = 'Coupon Price: ' + formatCurrency(data.apply_discount,
                                    '{{ $currency_setting }}');

                                var couponTag = $('<p>').text(coupon_code).addClass('h6');
                                sectionTitleDiv.append(couponTag);
                            }
                            var finalAmount = 'Final Price: ' + formatCurrency(data.final_amount,
                                '{{ $currency_setting }}');

                            var finalTag = $('<p>').text(finalAmount).addClass('h6');
                            sectionTitleDiv.append(finalTag);

                            if (data.hasOwnProperty('message')) {
                                toastrs('Success', data.message, 'success');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loader').fadeOut();
                        var errorMessage = xhr.responseJSON.error;
                        toastrs('error', errorMessage, 'Error');
                    }
                });
            });
        }
    </script>
@endpush
