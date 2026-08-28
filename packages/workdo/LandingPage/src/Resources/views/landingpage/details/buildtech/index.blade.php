
<div class="border rounded overflow-hidden mt-2 mb-3">
    {{ Form::open(array('route' => 'buildtech_store', 'method'=>'post', 'enctype' => "multipart/form-data",'class'=>'needs-validation','novalidate')) }}
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
                        {{ Form::label('buildtech heading', __('Heading'), ['class' => 'form-label']) }}
                        {{ Form::text('buildtech_heading', $settings['buildtech_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading'),'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('buildtech heading', __('Description'), ['class' => 'form-label']) }}
                        {{ Form::text('buildtech_description', $settings['buildtech_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description'),'required' => 'required']) }}
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
    <div class="p-3 border-bottom accordion-header" >
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">{{ __('BuildTech Section Cards') }}</h5>
            </div>
            <div class="col-auto justify-content-end">
                <a data-size="lg" data-url="{{ route('buildtech_card_create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip"  title="{{__('Create')}}" data-title="{{__('Create New Card')}}"  class="btn btn-sm btn-primary">
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
                    @if (is_array($buildtech_card_details) || is_object($buildtech_card_details))
                    @php
                        $ff_no = 1
                    @endphp
                        @foreach ($buildtech_card_details as $key => $value)
                            <tr>
                                <td>{{ $ff_no++ }}</td>
                                <td>{{ $value['buildtech_card_heading'] }}</td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <div class="action-btn">
                                                <a href="#" class="btn btn-sm bg-info align-items-center" data-url="{{ route('buildtech_card_edit',$key) }}" data-ajax-popup="true" data-title="{{__('Edit Card Detail')}}" data-size="lg" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>

                                        <div class="action-btn">
                                        {!! Form::open(['method' => 'GET', 'route' => ['buildtech_card_delete', $key],'id'=>'delete-form-'.$key]) !!}
                                            <a href="#" class="btn btn-sm bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{ 'delete-form-'.$key}}">
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
