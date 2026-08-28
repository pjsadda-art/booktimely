<div class="border rounded overflow-hidden mt-2 mb-3">
    <div class="p-3 border-bottom accordion-header">
        <div class="row">
            <div class="col accordion-header">
                <h5 class="mb-0">{{ __('Feature Head Details') }}</h5>
            </div>
        </div>
    </div>
    {{ Form::open(array('route' => 'feature_highlight_store', 'method'=>'post', 'enctype' => "multipart/form-data",'class' => 'needs-validation', 'novalidate')) }}
        @csrf
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('highlight_feature_heading', __('Heading'), ['class' => 'form-label']) }}
                        {{ Form::text('highlight_feature_heading', $settings['highlight_feature_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Link') , 'required' => 'required']) }}
                        @error('highlight_feature_heading')
                            <span class="invalid-mail_port" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('highlight_feature_heading', __('Description'), ['class' => 'form-label']) }}
                        {{ Form::text('highlight_feature_description', $settings['highlight_feature_description'], ['class' => 'form-control', 'placeholder' => __('Enter Link'), 'required' => 'required']) }}
                        @error('highlight_feature_description')
                        <span class="invalid-mail_port" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <input class="btn btn-print-invoice btn-primary" type="submit" value="{{ __('Save Changes') }}">
        </div>
    {{ Form::close() }}
</div>

<div class="border rounded overflow-hidden mb-3">
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col accordion-header">
                <h5 class="mb-0">{{ __('Feature Cards') }}</h5>
            </div>
            <div class="col-auto justify-content-end d-flex">
                <a data-size="lg" data-url="{{ route('feature_create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Card')}}"  class="btn btn-sm btn-primary">
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
                        <th>{{__('No')}}</th>
                        <th>{{__('Name')}}</th>
                        <th class="text-end">{{__('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                   @if (is_array($feature_of_features) || is_object($feature_of_features))
                   @php
                       $ff_no = 1
                   @endphp
                        @foreach ($feature_of_features as $key => $value)
                            <tr>
                                <td>{{ $ff_no++ }}</td>
                                <td>{{ $value['feature_heading'] }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div class="action-btn me-2">
                                                <a href="#" class="btn btn-sm bg-info align-items-center" data-url="{{ route('feature_edit',$key) }}" data-ajax-popup="true" data-title="{{__('Edit Card Detail')}}" data-size="lg" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>

                                        <div class="action-btn">
                                        {!! Form::open(['method' => 'GET', 'route' => ['feature_delete', $key],'id'=>'delete-form-'.$key]) !!}
                                            <a href="#" class="btn btn-sm  bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{ 'delete-form-'.$key}}">
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

<div class="border rounded overflow-hidden">
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">{{ __('Feature Sections') }}</h5>
            </div>
            <div class="col-auto justify-content-end d-flex">
                <a data-size="lg" data-url="{{ route('features_create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Section')}}"  class="btn btn-sm btn-primary">
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
                        <th>{{__('No')}}</th>
                        <th>{{__('Name')}}</th>
                        <th class="text-end">{{__('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (is_array($feature_of_features) || is_object($feature_of_features))
                    @php
                        $of_no = 1
                    @endphp
                        @foreach ($other_features as $key => $value)
                            <tr>
                                <td>{{ $of_no++ }}</td>
                                <td>{{ $value['other_features_heading'] }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div class="action-btn me-2">
                                                <a href="#" class="btn btn-sm   bg-info align-items-center" data-url="{{ route('features_edit',$key) }}" data-ajax-popup="true"   data-title="{{__('Edit New Section')}}" data-size="lg" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>

                                            <div class="action-btn">
                                            {!! Form::open(['method' => 'GET', 'route' => ['features_delete', $key],'id'=>'delete-form-'.$key]) !!}

                                                <a href="#" class="btn btn-sm bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{'delete-form-'.$key}}">
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

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
