{{ Form::open(array('route' => 'review_store', 'method'=>'post', 'enctype' => "multipart/form-data",'class' => 'needs-validation','novalidate')) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Header Tag', __('Header Tag'), ['class' => 'form-label']) }}
                    {{ Form::text('review_header_tag',null, ['class' => 'form-control ', 'placeholder' => __('Enter Header Tag'),'required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Form::text('review_heading',null, ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::textarea('review_description',null, ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'review_description','required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Live Demo button Link', __('Live Demo button Link'), ['class' => 'form-label']) }}
                    {{ Form::text('review_live_demo_link',null, ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group ">
                    {{ Form::label('Live Demo Button Text', __('Live Demo Button Text'), ['class' => 'form-label']) }}
                    {{ Form::text('review_live_demo_button_text',null, ['class' => 'form-control', 'placeholder' => __('Enter Button Text'),'required'=>'required']) }}
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer gap-3">
        <input type="button" value="{{__('Cancel')}}" class="btn m-0 btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn m-0 btn-primary">
    </div>
{{ Form::close() }}

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
