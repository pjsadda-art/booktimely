@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush

<div class="border rounded overflow-hidden mt-2 mb-3">
    {{ Form::open(['route' => 'faq.store', 'method' => 'post', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
    @csrf
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col-6">
                <h5 class="mb-0">{{ __('Main') }}</h5>
            </div>
        </div>
    </div>


    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Form::text('faq_heading', $settings['faq_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required' => 'required']) }}
                    @error('mail_host')
                        <span class="invalid-mail_driver" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::textarea('faq_description', $settings['faq_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'required' => 'required']) }}
                    @error('mail_port')
                        <span class="invalid-mail_port" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>



        </div>
    </div>
    <div class="card-footer text-end">
        <button class="btn btn-print-invoice btn-primary" type="submit">{{ __('Save Changes') }}</button>
    </div>
    {{ Form::close() }}

</div>


<div class="border rounded overflow-hidden mb-0">
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col-lg-9 col-md-9 col-sm-9">
                <div class="col-6">
                    <h5 class="mb-0">{{ __('Info') }}</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 justify-content-end d-flex">
                <a data-size="lg" data-url="{{ route('faq_create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
                    title="{{ __('Create') }}" class="btn btn-sm btn-primary">
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
                    @if (is_array($faqs) || is_object($faqs))
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($faqs as $key => $value)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $value['faq_questions'] }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div class="action-btn me-2">
                                            <a href="#" class="btn btn-sm  bg-info align-items-center"
                                                data-url="{{ route('faq_edit', $key) }}" data-ajax-popup="true"
                                                title="{{ __('Edit Page') }}" data-size="lg" data-bs-toggle="tooltip"
                                                title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>

                                        <div class="action-btn">
                                            {!! Form::open(['method' => 'GET', 'route' => ['faq_delete', $key], 'id' => 'delete-form-' . $key]) !!}
                                            <a href="#"
                                                class="btn btn-sm  bg-danger align-items-center bs-pass-para show_confirm"
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
