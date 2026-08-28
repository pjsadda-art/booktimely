<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5>{{ __('Custom JS and CSS') }}</h5>
            </div>
            <div id="p1" class="col-auto text-end text-primary h3">
            </div>
        </div>
    </div>
    <div class="card-body">
        {{--  Start for all settings tab --}}
        {{Form::model(null, array('route' => array('landingpage.custom-js-css.setting.save'), 'method' => 'POST')) }}
        @csrf
            <div class="border rounded overflow-hidden">
                <div class="">
                    <div class="row align-items-center row-gaps justify-content-between p-3">
                        <div class="mb-0 col-sm-6 col-12">
                            <div class="form-group mb-0">
                                {{ Form::label('landingpage_custom_js', __('Custom JS'), ['class' => 'col-form-label text-dark']) }}
                                {{ Form::textarea('landingpage_custom_js', isset($settings['landingpage_custom_js']) ? $settings['landingpage_custom_js'] : '', ['class' => 'form-control', 'id'=>'topbar_notification' ,'placeholder'=> __('console.log(hello);') ]) }}
                                <p class="text-danger mt-1 mb-0">{{ __('Note : Enter the Js, excluding the "<script>" and "</script>" tags.') }}</p>
                            </div>
                        </div>

                        <div class="mb-0 col-sm-6 col-12">
                            <div class="form-group mb-0">
                                {{ Form::label('landingpage_custom_css', __('Custom CSS'), ['class' => 'col-form-label text-dark']) }}
                                {{ Form::textarea('landingpage_custom_css', isset($settings['landingpage_custom_css']) ? $settings['landingpage_custom_css'] : '', ['class' => 'form-control', 'id'=>'topbar_notification', 'placeholder'=> __('<style>.body{color:aliceblue;}</style>')]) }}
                                <p class="text-danger mt-1 mb-0">{{ __('Note : Enter the Css, excluding the "<style>" and "</style>" tags.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer mt-3 text-end">
                    <input class="btn btn-print-invoice btn-primary " type="submit" value="{{ __('Save Changes') }}">
                </div>
            </div>
        {{ Form::close() }}
        {{--  End for all settings tab --}}
    </div>
</div>
