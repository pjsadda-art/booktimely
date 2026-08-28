{{Form::model($staff,array('route' => array('staff.update', $staff->id), 'method' => 'PUT', 'id' => 'business-edit-form','enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('name',__('Name'),['class'=>'form-label']) }}
                    {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
                    @error('name')
                    <small class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <div class="form-file">
                       {{Form::label('image',__('Image'),['class'=>'form-label']) }}
                        <input type="file" class="form-control mb-2" name="staff_image" id="staff_image" aria-label="file example" onchange="previewImage(this)">
                        <img class="rounded overflow-hidden" src="{{check_file($staff->user->avatar) ? get_file($staff->user->avatar): get_file('uploads/default/avatar.png')}}" id="blah" width="15%"/>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('email',__('Email'),['class'=>'form-label']) }}
                    {{Form::email('email',$staff->user->email,array('class'=>'form-control','placeholder'=>__('Enter Email'),'required'=>'required'))}}
                    @error('email')
                    <small class="invalid-email" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('loctaion',__('Locations'),['class'=>'form-label']) }}
                    {{ Form::select('location[]',$location, $staff->location_id , ['id' => 'location','class'=>"choices",'multiple'=>"",'searchEnabled'=>'true','required' => 'required' , 'placeholder' => 'Enter Locations']) }}
                    @permission('location create')
                        <div class=" text-xs mt-1">{{ __('Create location here. ') }}
                            <a href="#" data-ajax-popup="true" data-size="md"
                                data-title="{{ __('Create New Location') }}"
                                data-url="{{ route('location.create', ['business_id' => $staff->business_id]) }}"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                                <b>{{ __('Create location') }}</b>
                            </a>
                        </div>
                    @endpermission
                    @error('loctaion')
                    <small class="invalid-loctaion" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('service',__('Service'),['class'=>'form-label']) }}
                    {{ Form::select('service[]',$service, $staff->service_id , ['id' => 'service','class'=>"choices",'multiple'=>"",'searchEnabled'=>'true','required' => 'required','placeholder' => 'Enter Service']) }}
                    @permission('service create')
                        <div class=" text-xs mt-1">{{ __('Create service here. ') }}
                            <a href="#" data-ajax-popup="true" data-size="lg"
                                data-title="{{ __('Create New service') }}"
                                data-url="{{ route('service.create', ['business_id' => $staff->business_id]) }}"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                                <b>{{ __('Create service') }}</b>
                            </a>
                        </div>
                    @endpermission
                    @error('service')
                    <small class="invalid-service" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group mb-0">
                    {{Form::label('description',__('Description'),['class'=>'form-label']) }}
                    {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Description'),'required'=>'required','rows' => '4'))}}
                    @error('description')
                    <small class="invalid-description" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer gap-3">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn m-0 btn-primary'))}}
    </div>
{{Form::close()}}
