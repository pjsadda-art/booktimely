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
@push('script')
    <script>
        $(document).ready(function() {
            $(document).on('change', '#serviceSelect', function() {
                var selectedServiceId = $(this).val();

                $.ajax({
                    url: '{{ route('check.collaborative.service') }}',
                    method: 'GET',
                    data: {
                        service: selectedServiceId
                    },
                    success: function(data) {
                        var collaborative_service = data.collaborative_service;
                        var servicePrice = data.price;

                        if (collaborative_service == 1) {
                            $('#locationSelect').removeAttr('required');
                            $('#staffSelect').removeAttr('required');

                            $('#locationSelect').parent().css('display', 'none');
                            $('#staffSelect').parent().css('display', 'none');

                            var sectionTitleDiv = $(
                                '.appointment-form.payment-method-form .appointment_info_details'
                            );
                            sectionTitleDiv.children().not('#serviceAmount').remove();
                            $('#serviceAmount').html('Total Amount: ' + formatCurrency(
                                servicePrice, '{{ $currency_setting }}'));


                        } else {
                            $('#locationSelect').parent().css('display', '');
                            $('#staffSelect').parent().css('display', '');
                        }
                    },
                    error: function(status, error) {}
                });
            });
        })
    </script>
@endpush
