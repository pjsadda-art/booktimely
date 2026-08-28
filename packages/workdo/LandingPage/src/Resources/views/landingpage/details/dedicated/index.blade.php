<div class="border rounded overflow-hidden mt-2 mb-3">

    {{ Form::open(['route' => 'dedicated_store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
    @csrf
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">{{ __('Main') }}</h5>
            </div>
        </div>
    </div>


    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Dedicated heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Form::text('dedicated_heading', $settings['dedicated_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Dedicated heading', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::text('dedicated_description', $settings['dedicated_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Live', __('Live Demo Button Link'), ['class' => 'form-label']) }}
                    {{ Form::text('dedicated_live_demo_link', $settings['dedicated_live_demo_link'], ['class' => 'form-control ', 'placeholder' => __('Enter Details Link'), 'required' => 'required']) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Live Link Button Text', __('Live Demo Button Text'), ['class' => 'form-label']) }}
                    {{ Form::text('dedicated_link_button_text', $settings['dedicated_link_button_text'], ['class' => 'form-control', 'placeholder' => __('Enter Button Text'), 'required' => 'required']) }}
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer text-end">
        <input class="btn btn-print-invoice btn-primary" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}



</div>

<div class="border rounded overflow-hidden">
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">{{ __('Info') }}</h5>
            </div>
            <div class="col-auto justify-content-end d-flex">
                <a data-size="lg" data-url="{{ route('dedicated_card_create') }}" data-ajax-popup="true"
                    data-bs-toggle="tooltip" data-title="{{ __('Create New Card') }}" title="{{ __('Create') }}"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-plus text-light"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('No') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (is_array($dedicated_card_details) || is_object($dedicated_card_details))
                        @php
                            $ff_no = 1;
                        @endphp
                        @foreach ($dedicated_card_details as $key => $value)
                            <tr>
                                <td>{{ $ff_no++ }}</td>
                                <td>{{ $value['dedicated_card_heading'] }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div class="action-btn me-2">
                                            <a href="#" class="btn btn-sm  bg-info align-items-center"
                                                data-url="{{ route('dedicated_card_edit', $key) }}"
                                                data-ajax-popup="true" data-title="{{ __('Edit Card Detail') }}"
                                                data-size="lg" data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                data-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>

                                        <div class="action-btn">
                                            {!! Form::open(['method' => 'GET', 'route' => ['dedicated_card_delete', $key], 'id' => 'delete-form-' . $key]) !!}
                                            <a href="#"
                                                class="btn btn-sm align-items-center  bg-danger bs-pass-para show_confirm"
                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                data-original-title="{{ __('Delete') }}"
                                                data-confirm-yes="{{ 'delete-form-' . $key }}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
