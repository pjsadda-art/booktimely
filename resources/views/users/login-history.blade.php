<div class="modal-body">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('IP') }}</th>
                    <th>{{ __('Country') }}</th>
                    <th>{{ __('Device') }}</th>
                    <th>{{ __('OS') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $entry)
                    @php
                        $details = json_decode($entry->details);
                    @endphp
                    <tr>
                        <td>{{ !empty($entry->date) ? company_datetime_formate($entry->date) : '-' }}</td>
                        <td>{{ $entry->ip }}</td>
                        <td>{{ !empty($details->country) ? $details->country : '-' }}</td>
                        <td>{{ !empty($details->device_type) ? $details->device_type : '-' }}</td>
                        <td>{{ !empty($details->os_name) ? $details->os_name : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('No login history found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>
