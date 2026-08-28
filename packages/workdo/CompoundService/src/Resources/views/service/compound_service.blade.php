<div class="col-md-12 form-group">
    {{ Form::label('service_type', __('Service Type'), ['class' => 'form-label']) }}
    <select id="service_type" name="service_type" class="form-control" required>
        <option value="">{{ __("Select Service Type") }}</option>
        <option value="simple">{{ __('Simple') }}</option>
        <option value="compound">{{ __('Compound') }}</option>
    </select>
</div>

<div class="col-md-12">
    <div id="repeaterForm" class="form-group">
    {{ Form::label('Select Services', __('Select Services', ['class' => 'form-label'])) }}
        <button type="button" data-repeater-create class="btn btn-primary btn-sm float-end"><i
                class="ti ti-plus"></i></button>
        <div data-repeater-list="services" class="row">
            <div class="row my-2" data-repeater-item>
                <div class="col-md-10">
                    <select name="services" id="services" class="form-control" required>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{{ $service->name }}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" data-repeater-delete><i
                            class="ti ti-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script>
    $(document).ready(function() {
        function handleServiceTypeChange(selectedOption) {
            if (selectedOption === 'compound') {
                // Remove the 'required' attribute from the duration field
                $('input[name="duration"]').removeAttr('required');
                $('[name="duration"]').closest('.form-group').hide();
                $('#repeaterForm').show();
            } else {
                $('[name="duration"]').closest('.form-group').show();
                $('#repeaterForm').hide();
            }
        }
        // Call the function on page load
        var selectedOption = $('#service_type').val();
        handleServiceTypeChange(selectedOption);

        // Define the change event handler
        $('#service_type').on('change', function() {
            var selectedOption = $(this).val();
            handleServiceTypeChange(selectedOption);
        });

        $('#repeaterForm').repeater({
            show: function() {
                $(this).slideDown();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            }
        });
        $('#repeaterForm [data-repeater-item]:first').find('[data-repeater-delete]').remove();
    });
</script>
