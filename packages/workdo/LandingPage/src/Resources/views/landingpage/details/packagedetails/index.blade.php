<div class="border rounded overflow-hidden mt-2">
    {{ Form::open(array('route' => 'packagedetails_store', 'method'=>'post', 'enctype' => "multipart/form-data",'class'=>'needs-validation','novalidate')) }}
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
                        {{ Form::label('packagedetails heading', __('Heading'), ['class' => 'form-label']) }}
                        {{ Form::text('packagedetails_heading', $settings['packagedetails_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading'),'required'=>'required']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('packagedetails Description', __('Short Description'), ['class' => 'form-label']) }}
                        {{ Form::text('packagedetails_short_description', $settings['packagedetails_short_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description'),'required'=>'required']) }}
                    </div>
                </div>
                <div class="form-group col-12">
                    {{ Form::label('packagedetails Description', __('Long Description'), ['class' => 'col-form-label text-dark']) }}
                    {{ Form::textarea('packagedetails_long_description',$settings['packagedetails_long_description'], ['class' => 'summernote form-control', 'required' => 'required', 'id'=>'packagedetails']) }}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('Package ', __('Package Link'), ['class' => 'form-label']) }}
                        {{ Form::text('packagedetails_link',$settings['packagedetails_link'], ['class' => 'form-control ', 'placeholder' => __('Enter Details Link')]) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('Live Link Button Text', __('Live Demo Button Text'), ['class' => 'form-label']) }}
                        {{ Form::text('packagedetails_button_text',$settings['packagedetails_button_text'], ['class' => 'form-control', 'placeholder' => __('Enter Button Text'),'required'=>'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <input class="btn btn-print-invoice btn-primary" type="submit" value="{{ __('Save Changes') }}">
        </div>
    {{ Form::close() }}
</div>
