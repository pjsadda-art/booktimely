{{Form::model(null, array('route' => array('faq_update', $key), 'method' => 'POST','enctype' => "multipart/form-data",'class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('questions', __('Questions'), ['class' => 'form-label']) }}
                {{ Form::text('faq_questions',$faq['faq_questions'], ['class' => 'form-control ', 'placeholder' => __('Enter Questions'),'required'=>'required']) }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-0">
                {{ Form::label('answer', __('Answer'), ['class' => 'form-label']) }}
                {{ Form::textarea('faq_answer', $faq['faq_answer'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Answer'), 'id'=>'summernote','required'=>'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer gap-3">
    <input type="button" value="{{__('Cancel')}}" class="btn m-0  btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  m-0 btn-primary">
</div>
{{ Form::close() }}
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
