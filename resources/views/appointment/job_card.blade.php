@if ($error)
    <div class="modal-body">
        <div class="alert alert-danger mb-0">{{ $error }}</div>
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@else
    <div class="modal-body">
        <div id="job-card-popup-files-{{ $appointment->id }}" class="mb-2">
            @forelse (optional($appointment->jobCard)->files ?? [] as $file)
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
                <p class="text-muted small mb-0" id="job-card-popup-empty-{{ $appointment->id }}">
                    {{ __('No job card attached yet.') }}
                </p>
            @endforelse
        </div>

        @permission('appointment edit')
            <div class="input-group input-group-sm">
                <input type="file" class="form-control" id="job-card-popup-upload-{{ $appointment->id }}" multiple
                    accept=".jpg,.jpeg,.png,.pdf">
                <button type="button" class="btn btn-outline-primary"
                    id="job-card-popup-upload-btn-{{ $appointment->id }}"
                    data-url="{{ route('job-card.store', $appointment->id) }}">
                    <i class="ti ti-upload me-1"></i>{{ __('Upload') }}
                </button>
            </div>
            <small class="text-muted d-block mt-1">{{ __('JPG, PNG or PDF, up to 10MB each.') }}</small>
        @endpermission
    </div>
    <div class="modal-footer gap-3 pt-3 p-0">
        <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>

    <script>
        // Inline rather than pushed to the scripts stack: this view is loaded
        // into the shared modal by ajax, so pushed scripts never reach the layout.
        (function () {
            var uploadBtn = document.getElementById('job-card-popup-upload-btn-{{ $appointment->id }}');
            var fileInput = document.getElementById('job-card-popup-upload-{{ $appointment->id }}');
            var list = document.getElementById('job-card-popup-files-{{ $appointment->id }}');
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
                var empty = document.getElementById('job-card-popup-empty-{{ $appointment->id }}');
                if (empty) { empty.remove(); }
            }

            function showEmptyNoteIfNone() {
                if (!list.querySelector('[data-job-card-file-row]')) {
                    var p = document.createElement('p');
                    p.className = 'text-muted small mb-0';
                    p.id = 'job-card-popup-empty-{{ $appointment->id }}';
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
@endif
