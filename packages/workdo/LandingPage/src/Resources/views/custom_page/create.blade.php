
{{ Form::open(array('route' => 'custom_page.store', 'method'=>'post', 'enctype' => "multipart/form-data",'class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="form-group col-md-12">
                {{Form::label('name',__('Page Name'),['class'=>'form-label'])}}
                {{Form::text('menubar_page_name',null,array('class'=>'form-control font-style','placeholder'=>__('Enter Page Name'),'required'=>'required'))}}
            </div>

            <div class="form-group">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="template_name" value="page_content"
                           id="page_content" data-name="page_content" checked>
                    <label class="form-check-label" for="page_content">
                        {{ 'Page Content' }}
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="template_name" value="page_url" id="page_url"
                           data-name="page_url">
                    <label class="form-check-label" for="page_url">
                        {{ 'Page URL' }}
                    </label>
                </div>
            </div>

            <div class="form-group col-md-12 page_url d-none">
                {{ Form::label('page_url', __('Page URL'), ['class' => 'form-label']) }}
                {{ Form::text('page_url', null, ['class' => 'form-control font-style', 'id' => 'page_url_input', 'placeholder' => __('Enter Page URL')]) }}
            </div>

            <div class="form-group col-md-12 page_content">
                {{Form::label('name',__('Page Short Description'),['class'=>'form-label'])}}
                {{ Form::text('menubar_page_short_description', null, array('class'=>'form-control font-style','id' => 'menubar_page_short_description' ,'required' => 'required', 'placeholder'=>__('Enter Page Short Description'))) }}
            </div>

            <div class="form-group col-md-12 page_content">
                {{ Form::label('description', __('Page Content'), ['class' => 'form-label']) }}
                {!! Form::textarea('menubar_page_contant', null, [
                    'class' => 'summernote form-control',
                    'rows' => '5',
                    'id' => 'menubar_page_content',
                    'required' => 'required'
                ]) !!}
            </div>
            <div class="col-12">
                <div class="row row-gaps">
            <div class="col-auto">
                <div class="form-check form-switch ml-1">
                    <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="header" />
                    <label class="form-check-label f-w-600 pl-1" for="cust-theme-bg" >{{__('Header')}}</label>
                </div>
            </div>

            <div class="col-auto">
                <div class="form-check form-switch ml-1">
                    <input type="checkbox" class="form-check-input" id="cust-darklayout" name="footer"/>
                    <label class="form-check-label f-w-600 pl-1" for="cust-darklayout">{{ __('Footer') }}</label>
                </div>
            </div>

            <div class="col-auto">
                <div class="form-check form-switch ml-1">
                    <input type="checkbox" class="form-check-input" id="cust-darklayout" name="login"/>
                    <label class="form-check-label f-w-600 pl-1" for="cust-darklayout">{{ __('Login') }}</label>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer gap-3">
        <input type="button" value="{{__('Cancel')}}" class="btn m-0 btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn m-0 btn-primary">
    </div>
{{ Form::close() }}


<script>
    $(document).ready(function() {
        $('input[name="template_name"][id="page_content"]').prop('checked', true);
        $('input[name="template_name"]').trigger("change");
    });

    $('input[name="template_name"]').change(function() {
        var radioValue = $('input[name="template_name"]:checked').val();

        if (radioValue === "page_content") {
            $('.page_content').removeClass('d-none');
            $('.page_url').addClass('d-none');
            $('#page_url_input').removeAttr('required');
            $('#menubar_page_short_description').attr('required', 'required');
            $('#menubar_page_content').attr('required', 'required');
        } else {
            $('.page_content').addClass('d-none');
            $('.page_url').removeClass('d-none');
            $('#page_url_input').attr('required', 'required');
            $('#menubar_page_short_description').removeAttr('required');
            $('#menubar_page_content').removeAttr('required');
        }
    });
</script>
