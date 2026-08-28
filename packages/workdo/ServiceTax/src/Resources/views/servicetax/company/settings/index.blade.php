<div class="card" id="service-tax-sidenav">
    {{ Form::open(['route' => 'servicetax.setting.save', 'method' => 'post']) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('Service Tax Option Settings') }}</h5>
                <small>{{ __('Edit your service tax option settings') }}</small>
            </div>
        </div>
    </div>
    <div class="card-body pb-0">
        <div class="d-flex">
            <div class="col-sm-6 col-12">
                <div class="form-group col switch-width">
                    <div>
                        @php
                        $taxOption = isset($settings->value) ? $settings->value : 'inclusive';
                        @endphp

                        <div class="form-check form-check-inline">
                            {{ Form::radio('service_tax_option', 'inclusive', $taxOption === 'inclusive', ['id' => 'service_tax_inclusive', 'class' => 'form-check-input']) }}
                            {{ Form::label('service_tax_inclusive', __('Inclusive'), ['class' => 'form-check-label ms-1 me-3']) }}
                        </div>

                        <div class="form-check form-check-inline">
                            {{ Form::radio('service_tax_option', 'exclusive', $taxOption === 'exclusive', ['id' => 'service_tax_exclusive', 'class' => 'form-check-input']) }}
                            {{ Form::label('service_tax_exclusive', __('Exclusive'), ['class' => 'form-check-label ms-1']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}

</div>