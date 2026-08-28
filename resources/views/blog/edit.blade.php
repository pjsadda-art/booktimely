
    {{Form::model($blog,array('route' => array('blogs.update', $blog->id), 'method' => 'PUT','enctype' => 'multipart/form-data')) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('title',__('Title'),['class'=>'form-label']) }}
                    {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Blog Title'),'required'=>'required'))}}
                    @error('title')
                    <small class="invalid-title" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('blog_image',__('Image'),['class'=>'form-label']) }}
                    <div class="choose-files ">
                        <img id="blah" width="100" class="rounded overflow-hidden me-2"
                        src="{{check_file($blog->image) ? get_file($blog->image): get_file('uploads/default/avatar.png')}}" />
                        <label for="blog_image">
                            <div class=" bg-primary "> <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>

                                <input type="file" class="form-control file" name="blog_image"
                                              id="blog_image" data-filename="blog_image"
                                              onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('date',__('Date'),['class'=>'form-label'])}}
                    {{Form::date('date',null,array('class'=>'form-control','placeholder'=>__('Select Date')))}}
                    @error('date')
                    <small class="invalid-mobile" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                    @enderror
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
