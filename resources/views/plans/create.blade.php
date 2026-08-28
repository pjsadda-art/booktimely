
{{ Form::open(['route' => 'plan.store', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control','required'=>'required', 'placeholder' => __('Enter Plan Name')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('is_free_plan', __('Plan Type'), ['class' => 'form-label']) }}
                {{ Form::select('is_free_plan',$plan_type, null, ['class' => 'form-control','required'=>'required','id'=>'is_free_plan', 'placeholder' => __('--- Select Plan Type ---')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('number_of_user', __('Number of User'), ['class' => 'form-label']) }}
                {{ Form::number('number_of_user', null, ['class' => 'form-control','required'=>'required','placeholder' => __('Number of User'),'step' => '1','id'=>'number_of_user']) }}
                <span class="small text-danger mt-2 d-block">{{__('Note: "-1" for Unlimited')}}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('number_of_business', __('Number of Business'), ['class' => 'form-label']) }}
                {{ Form::number('number_of_business', null, ['class' => 'form-control','required'=>'required','placeholder' => __('Number of Business'),'step' => '1','id'=>'number_of_business']) }}
                <span class="small text-danger mt-2 d-block">{{__('Note: "-1" for Unlimited')}}</span>
            </div>
        </div>
        <div class="col-md-6 plan_price_div">
            <div class="form-group">
                {{ Form::label('package_price_monthly', __('Basic Package Price/Month').' ( '.company_setting('defult_currancy_symbol').' )', ['class' => 'form-label add_lable']) }}
                {{ Form::number('package_price_monthly',null, ['class' => 'form-control ','placeholder' => __('Price/month'),'step' => '0.1','min'=>'0']) }}
            </div>
        </div>
        <div class="col-md-6 plan_price_div">
            <div class="form-group">
                {{ Form::label('package_price_yearly', __('Basic Package Price/Year').' ( '.company_setting('defult_currancy_symbol').' )', ['class' => 'form-label add_lable']) }}
                {{ Form::number('package_price_yearly',null, ['class' => 'form-control','placeholder' => __('Price/Yearly'),'step' => '0.1','min'=>'0']) }}
            </div>
        </div>
        <div class="col-md-6 plan_price_div">
            <label class="form-check-label" for="trial"></label>
            <div class="form-group">
                <label for="trial" class="form-label">{{ __('Trial is enable(on/off)') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="trial" class="form-check-input input-primary pointer" value="1" id="trial" {{ company_setting('trial')=='on'?' checked ':'' }}>
                    <label class="form-check-label" for="trial"></label>
                </div>
            </div>
        </div>
        <div class="col-md-6 d-none plan_div plan_price_div">
            <div class="form-group">
                {{ Form::label('trial_days', __('Trial Days'), ['class' => 'form-label']) }}
                {{ Form::number('trial_days',null, ['class' => 'form-control','placeholder' => __('Enter Trial days'),'step' => '1','min'=>'1']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('add_on', __('Add-on'), ['class' => 'form-label mb-0 h5']) }}
            </div>
        </div>
        @if (count($modules))
            @foreach ($modules as $module)
                @if (!isset($module->display) || $module->display == true)
                    <div class="col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card w-100">
                            <div class="card-body p-3">
                                <div class="d-flex gap-2 align-items-center justify-content-between w-100 h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar">
                                            <img src="{{ $module->image }}{{'?'.time()}}" alt="{{ $module->name }}" class="img-user rounded width-100">
                                        </div>
                                        <div class="ms-2">
                                            <label for="modules_{{ $module->name }}">
                                                <h5 class="mb-0 pointer text-break">{{ $module->alias }}</h5>
                                            </label>
                                            <p class="text-muted text-sm mb-0">
                                                {{ isset($module->description) ? $module->description : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input modules" name="modules[]" value="{{$module->name}}" id="modules_{{ $module->name }}" type="checkbox">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="col-lg-12 col-md-12">
                <div class="card p-3">
                    <div class="d-flex justify-content-center">
                        <div class="ms-3 text-center">
                            <h3>{{ __('Add-on Not Available') }}</h3>
                            <p class="text-muted">{{ __('Click ') }}<a
                                    href="{{route('module.index') }}">{{ __('here') }}</a>
                                {{ __('To Acctive Add-on') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer gap-3">
    <button type="button" class="btn btn-secondary m-0" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary m-0'))}}
</div>
{{Form::close()}}

