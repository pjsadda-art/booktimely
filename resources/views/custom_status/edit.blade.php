{{Form::model($customStatus,array('route' => array('custom-status.update', $customStatus->id), 'method' => 'PUT','enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="text-start mb-3">
        @if (module_is_active('AIAssistant'))
            @php
                $admin_settings = getAdminAllSetting();
            @endphp
            @if (module_is_active('AIAssistant') && !empty($admin_settings['chatgpt_is']) && $admin_settings['chatgpt_is'] == 'on')
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'custom status','module'=>'General'])
            @endif
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Title'), 'required' => 'required']) }}
                @error('title')
                    <small class="invalid-title" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-0">
                {{ Form::label('status_color', __('Status Color'), ['class' => 'form-label']) }}
                <input class="jscolor form-control" value="{{ $customStatus->status_color }}" name="status_color" id="color" required>
                @error('status_color')
                    <small class="invalid-status_color" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <div class="form-check form-switch custom-switch-v1">
                <label class="form-check-label" for="send_sms">{{ __('SMS Send') }}</label>
                <input type="checkbox" class="form-check-input input-primary" name="send_sms"
                    id="send_sms" {{ $customStatus->send_sms ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <div class="form-check form-switch custom-switch-v1">
                <label class="form-check-label" for="send_email">{{ __('Email Send') }}</label>
                <input type="checkbox" class="form-check-input input-primary" name="send_email"
                    id="send_email" {{ $customStatus->send_email ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <div class="form-check form-switch custom-switch-v1">
                <label class="form-check-label" for="show_in_kanban">{{ __('Show in Kanban') }}</label>
                <input type="checkbox" class="form-check-input input-primary" name="show_in_kanban"
                    id="show_in_kanban" {{ $customStatus->show_in_kanban ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer gap-3">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn m-0 btn-primary']) }}
</div>
{{ Form::close() }}
