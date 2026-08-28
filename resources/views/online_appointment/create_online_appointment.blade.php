<form action="{{ route('save.online.meeting.setting', ['serviceId' => $service->id]) }}" method="post">
    <div class="modal-body">
        <div class="row row-gaps">
            @csrf
            <div class="col-sm-6 col-12">
                @stack('zoommeeting')
            </div>
            <div class="col-sm-6 col-12">
                @stack('googlemeeting')
            </div>
        </div>
    </div>
    <div class="modal-footer gap-3">
        <button type="submit" class="btn btn-primary m-0 btn-lg">{{ __('Create') }}</button>
    </div>
</form>
