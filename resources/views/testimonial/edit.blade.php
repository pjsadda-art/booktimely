
    {{Form::model($testimonial,array('route' => array('testimonials.update', $testimonial->id), 'method' => 'PUT','enctype' => 'multipart/form-data')) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('', __('Customer'), ['class' => 'form-label']) !!}
                    {!! Form::select('customer_id', $customers, null, ['class' => 'form-control', 'data-role' => 'tagsinput', 'id' => 'user_id']) !!}
                    @error('customer_id')
                    <small class="invalid-customer_id" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('testimonial_image',__('Image'),['class'=>'form-label']) }}
                    <div class="choose-files ">
                        <img id="blah" width="100" class="rounded overflow-hidden"
                        src="{{check_file($testimonial->image) ? get_file($testimonial->image): get_file('uploads/default/avatar.png')}}" />
                        <label for="testimonial_image">
                            <div class=" bg-primary "> <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>

                                <input type="file" class="form-control file" name="testimonial_image"
                                              id="testimonial_image" data-filename="testimonial_image"
                                              onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group mb-0">
                    {{Form::label('description',__('Description'),['class'=>'form-label']) }}
                    {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Description'),'rows' => '4'))}}
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
