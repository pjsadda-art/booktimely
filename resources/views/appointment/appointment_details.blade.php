<div class="modal-body">


    <div class="row-gaps appointment-detail-popup">
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Customer') }} : </div>
            <p class="text-md mb-0">
                {{ !empty($appointments->CustomerData) ? $appointments->CustomerData->name : ($appointments->name ?? 'Guest') . ' (Guest)' }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Staff') }} :</div>
            <p class="text-md mb-0">
                {{ !empty($appointments->StaffData) ? $appointments->StaffData->name : '-' }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Service') }} :</div>
            <p class="text-md mb-0">
                {{ !empty($appointments->ServiceData) ? $appointments->ServiceData->name : '-' }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Location') }} :</div>
            <p class="text-md mb-0">
                {{ !empty($appointments->LocationData) ? $appointments->LocationData->name : '-' }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Payment Type') }} :</div>
            <p class="text-md mb-0">
                {{ !empty($appointments->payment_type) ? $appointments->payment_type : '-' }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Status') }} :</div>
            <p class="text-md mb-0">
                {{ !empty($appointments->StatusData) ? $appointments->StatusData->title : (module_is_active('WaitingList') && $appointments->appointment_status == 'Waiting List' ? $appointments->appointment_status : 'Pending') }}
            </p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Date') }} :</div>
            <p class="text-md mb-0">{{ $appointments->date }}</p>
        </div>
        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Time') }}:</div>
            <p class="text-md mb-0">{{ $appointments->time }}</p>
        </div>
        {{-- Deposit --}}
        @php
            $currency = company_setting('defult_currancy_symbol', null, $appointments->business_id) ?: '$';
            $depositBadge = [
                'none' => 'secondary',
                'pending' => 'warning',
                'paid' => 'success',
                'forfeited' => 'dark',
                'refunded' => 'info',
            ][$deposit['status']] ?? 'secondary';
        @endphp

        <hr class="my-3">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">{{ __('Deposit') }}</h6>
            <span class="badge bg-{{ $depositBadge }}">{{ __(ucfirst($deposit['status'])) }}</span>
        </div>

        <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
            <div class="form-control-label text-md mb-0 h6">{{ __('Deposit Required') }} :</div>
            <p class="text-md mb-0">{{ $deposit['required'] ? __('Yes') : __('No') }}</p>
        </div>

        @if ($deposit['status'] !== 'none')
            <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
                <div class="form-control-label text-md mb-0 h6">{{ __('Deposit Amount') }} :</div>
                <p class="text-md mb-0">
                    {{ $currency }}{{ number_format((float) $deposit['amount'], 2) }}
                    @if ($deposit['percentage'])
                        <span class="text-muted">({{ rtrim(rtrim(number_format((float) $deposit['percentage'], 2), '0'), '.') }}%)</span>
                    @endif
                </p>
            </div>

            <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
                <div class="form-control-label text-md mb-0 h6">{{ __('Requested By') }} :</div>
                {{-- Null requested_by means the automatic rule engine raised it. --}}
                <p class="text-md mb-0">{{ $deposit['requested_by'] ?? __('Automatic rule') }}</p>
            </div>

            @if ($deposit['status'] === 'paid' && $deposit['method'])
                <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
                    <div class="form-control-label text-md mb-0 h6">{{ __('Paid By') }} :</div>
                    <p class="text-md mb-0">
                        {{ __(ucfirst(str_replace('_', ' ', $deposit['method']))) }}
                        @if ($deposit['paid_at'])
                            <span class="text-muted">· {{ $deposit['paid_at']->format('d M Y H:i') }}</span>
                        @endif
                    </p>
                </div>
            @endif

            @if ($deposit['status'] === 'forfeited' && $deposit['forfeit_reason'])
                <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
                    <div class="form-control-label text-md mb-0 h6">{{ __('Forfeit Reason') }} :</div>
                    <p class="text-md mb-0">{{ $deposit['forfeit_reason'] }}</p>
                </div>
            @endif

            @if ($deposit['status'] === 'refunded' && $deposit['refund_reference'])
                <div class="appointment-detail-item d-flex flex-wrap align-items-start gap-2">
                    <div class="form-control-label text-md mb-0 h6">{{ __('Refund Reference') }} :</div>
                    <p class="text-md mb-0">{{ $deposit['refund_reference'] }}</p>
                </div>
            @endif
        @endif

        @if ($deposit['status'] === 'pending' && !empty($deposit['link']))
            <div class="mt-2">
                <label class="form-label text-md mb-1">{{ __('Payment Link') }}</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" readonly value="{{ $deposit['link'] }}"
                        id="deposit-link-{{ $appointments->id }}">
                    <button type="button" class="btn btn-outline-secondary"
                        data-deposit-copy="deposit-link-{{ $appointments->id }}">{{ __('Copy') }}</button>
                    @permission('deposit manage')
                        <button type="button" class="btn btn-outline-warning"
                            data-deposit-regenerate="{{ route('deposit.regenerate', $appointments->id) }}"
                            data-target-input="deposit-link-{{ $appointments->id }}">{{ __('Regenerate') }}</button>
                    @endpermission
                </div>
                <small class="text-muted d-block mt-1" id="deposit-link-note-{{ $appointments->id }}">
                    {{ __('Regenerating issues a new link and stops the old one working.') }}
                </small>
            </div>
        @endif

        @permission('deposit manage')
            @if (!$deposit['lock']['allowed'])
                <div class="alert alert-warning py-2 mt-3 mb-0 small">
                    <i class="ti ti-lock me-1"></i>{{ $deposit['lock']['reason'] }}
                </div>
            @else
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if ($deposit['status'] === 'none')
                        @if ($deposit['can_request']['allowed'])
                            <a href="#" class="btn btn-sm btn-warning" data-ajax-popup="true" data-size="md"
                                data-title="{{ __('Request Deposit Payment') }}"
                                data-url="{{ route('deposit.create', $appointments->id) }}">
                                <i class="ti ti-cash me-1"></i>{{ __('Request Deposit Payment') }}
                            </a>
                        @else
                            {{-- Disabled with the reason on it rather than hidden:
                                 staff need to know why, or it reads as a bug. --}}
                            <span data-bs-toggle="tooltip"
                                data-bs-original-title="{{ $deposit['can_request']['reason'] }}">
                                <button type="button" class="btn btn-sm btn-warning" disabled>
                                    <i class="ti ti-cash me-1"></i>{{ __('Request Deposit Payment') }}
                                </button>
                            </span>
                        @endif
                    @endif
                    @if (in_array($deposit['status'], ['none', 'pending']))
                        {{-- Only the paid-in-full lockout gates this one, and that
                             is already handled by the branch above. --}}
                        <a href="#" class="btn btn-sm btn-success" data-ajax-popup="true" data-size="md"
                            data-title="{{ __('Manual Deposit') }}"
                            data-url="{{ route('deposit.payment-form', $appointments->id) }}">
                            <i class="ti ti-credit-card me-1"></i>{{ __('Manual Deposit') }}
                        </a>
                    @endif
                    @if (in_array($deposit['status'], ['pending', 'paid']))
                        <a href="#" class="btn btn-sm btn-dark" data-ajax-popup="true" data-size="md"
                            data-title="{{ __('Forfeit Deposit') }}"
                            data-url="{{ route('deposit.forfeit-form', $appointments->id) }}">
                            <i class="ti ti-ban me-1"></i>{{ __('Forfeit') }}
                        </a>
                    @endif
                    @if ($deposit['status'] === 'paid')
                        <a href="#" class="btn btn-sm btn-secondary" data-ajax-popup="true" data-size="md"
                            data-title="{{ __('Refund Deposit') }}"
                            data-url="{{ route('deposit.refund-form', $appointments->id) }}">
                            <i class="ti ti-arrow-back-up me-1"></i>{{ __('Refund') }}
                        </a>
                    @endif
                </div>
            @endif
        @endpermission

        <script>
            // Inline rather than pushed to the scripts stack: this view is loaded
            // into a modal by ajax, and @push never reaches the parent layout.
            (function () {
                document.querySelectorAll('[data-deposit-copy]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var input = document.getElementById(button.getAttribute('data-deposit-copy'));
                        input.select();
                        // Clipboard API needs a secure context; fall back so this
                        // still works when the admin is served over plain HTTP.
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(input.value);
                        } else {
                            document.execCommand('copy');
                        }
                        button.textContent = @json(__('Copied'));
                        setTimeout(function () { button.textContent = @json(__('Copy')); }, 1500);
                    });
                });

                document.querySelectorAll('[data-deposit-regenerate]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        if (!confirm(@json(__('Issue a new link? The current one will stop working.')))) { return; }

                        var input = document.getElementById(button.getAttribute('data-target-input'));
                        var note = input.closest('.mt-2').querySelector('small');

                        button.disabled = true;

                        fetch(button.getAttribute('data-deposit-regenerate'), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(function (response) { return response.json(); })
                        .then(function (body) {
                            if (body.success && body.link) { input.value = body.link; }
                            note.textContent = body.message;
                            note.className = body.success ? 'small text-success d-block mt-1' : 'small text-danger d-block mt-1';
                        })
                        .catch(function () {
                            note.textContent = @json(__('The request failed. Try again.'));
                            note.className = 'small text-danger d-block mt-1';
                        })
                        .finally(function () { button.disabled = false; });
                    });
                });
            })();
        </script>

        <hr class="my-3">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">{{ __('Job Card') }}</h6>
        </div>

        <div id="job-card-files-{{ $appointments->id }}" class="mb-2">
            @forelse (optional($appointments->jobCard)->files ?? [] as $file)
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1"
                    data-job-card-file-row="{{ $file->id }}">
                    <a href="{{ check_file($file->file_path) ? get_file($file->file_path) : '#' }}" target="_blank"
                        class="text-truncate me-2">
                        <i class="ti ti-file-text me-1"></i>{{ $file->original_name }}
                    </a>
                    @permission('appointment edit')
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            data-job-card-delete="{{ route('job-card.file.destroy', $file->id) }}">
                            <i class="ti ti-trash"></i>
                        </button>
                    @endpermission
                </div>
            @empty
                <p class="text-muted small mb-0" id="job-card-empty-{{ $appointments->id }}">
                    {{ __('No job card attached yet.') }}
                </p>
            @endforelse
        </div>

        @permission('appointment edit')
            <div class="input-group input-group-sm">
                <input type="file" class="form-control" id="job-card-upload-{{ $appointments->id }}" multiple
                    accept=".jpg,.jpeg,.png,.pdf">
                <button type="button" class="btn btn-outline-primary" id="job-card-upload-btn-{{ $appointments->id }}"
                    data-url="{{ route('job-card.store', $appointments->id) }}">
                    <i class="ti ti-upload me-1"></i>{{ __('Upload') }}
                </button>
            </div>
            <small class="text-muted d-block mt-1">{{ __('JPG, PNG or PDF, up to 10MB each.') }}</small>
        @endpermission

        <script>
            // Inline for the same reason as the deposit script above: this view
            // is loaded into the modal by ajax, so @@push never reaches the layout.
            (function () {
                var uploadBtn = document.getElementById('job-card-upload-btn-{{ $appointments->id }}');
                var fileInput = document.getElementById('job-card-upload-{{ $appointments->id }}');
                var list = document.getElementById('job-card-files-{{ $appointments->id }}');
                var deleteUrlTpl = @json(route('job-card.file.destroy', ['id' => '__ID__']));

                function fileRow(file) {
                    var div = document.createElement('div');
                    div.className = 'd-flex align-items-center justify-content-between border rounded p-2 mb-1';
                    div.setAttribute('data-job-card-file-row', file.id);

                    var a = document.createElement('a');
                    a.href = file.url || '#';
                    a.target = '_blank';
                    a.className = 'text-truncate me-2';
                    a.innerHTML = '<i class="ti ti-file-text me-1"></i>' + file.name;
                    div.appendChild(a);

                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-outline-danger';
                    btn.setAttribute('data-job-card-delete', deleteUrlTpl.replace('__ID__', file.id));
                    btn.innerHTML = '<i class="ti ti-trash"></i>';
                    div.appendChild(btn);

                    return div;
                }

                function clearEmptyNote() {
                    var empty = document.getElementById('job-card-empty-{{ $appointments->id }}');
                    if (empty) { empty.remove(); }
                }

                function showEmptyNoteIfNone() {
                    if (!list.querySelector('[data-job-card-file-row]')) {
                        var p = document.createElement('p');
                        p.className = 'text-muted small mb-0';
                        p.id = 'job-card-empty-{{ $appointments->id }}';
                        p.textContent = @json(__('No job card attached yet.'));
                        list.appendChild(p);
                    }
                }

                if (uploadBtn) {
                    uploadBtn.addEventListener('click', function () {
                        if (!fileInput.files.length) { return; }

                        var formData = new FormData();
                        for (var i = 0; i < fileInput.files.length; i++) {
                            formData.append('files[]', fileInput.files[i]);
                        }

                        uploadBtn.disabled = true;

                        fetch(uploadBtn.getAttribute('data-url'), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (!res.ok) {
                                    toastrs('Error', res.data.error || @json(__('Failed to upload job card.')), 'error');
                                    return;
                                }

                                clearEmptyNote();
                                res.data.files.forEach(function (file) { list.appendChild(fileRow(file)); });
                                fileInput.value = '';
                                toastrs('Success', @json(__('Job card uploaded.')), 'success');
                            })
                            .catch(function () {
                                toastrs('Error', @json(__('Failed to upload job card.')), 'error');
                            })
                            .finally(function () { uploadBtn.disabled = false; });
                    });
                }

                list.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-job-card-delete]');
                    if (!btn) { return; }
                    if (!confirm(@json(__('Delete this job card file?')))) { return; }

                    fetch(btn.getAttribute('data-job-card-delete'), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                        .then(function (res) {
                            if (!res.ok) {
                                toastrs('Error', res.data.error || @json(__('Failed to delete file.')), 'error');
                                return;
                            }
                            btn.closest('[data-job-card-file-row]').remove();
                            showEmptyNoteIfNone();
                            toastrs('Success', @json(__('File deleted.')), 'success');
                        })
                        .catch(function () {
                            toastrs('Error', @json(__('Failed to delete file.')), 'error');
                        });
                });
            })();
        </script>

         @if (!empty($appointments->custom_field))
        @php
            $customfields = json_decode($appointments->custom_field, true);
        @endphp
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3">{{ __('Custom Details') }}</h5>
                @foreach ($customfields as $key => $value)
                    <dl class="row align-items-center">
                        <dt class="col-sm-5 h6 mb-0">{{ $key }}:</dt>
                        <dd class="col-sm-7 mb-0">
                            {{ $value }}
                        </dd>
                    </dl>
                @endforeach
            </div>
        </div>
    @endif
    </div>
</div>
