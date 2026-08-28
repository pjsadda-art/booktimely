@if (empty($data))
    <p class="text-muted mb-0">{{ __('No job cards yet.') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Appointment') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Files') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $jobCard)
                    <tr>
                        <td>#{{ $jobCard->appointment_id }}</td>
                        <td>{{ optional($jobCard->appointment)->date ?? '-' }}</td>
                        <td>
                            @forelse ($jobCard->files as $file)
                                <a href="{{ check_file($file->file_path) ? get_file($file->file_path) : '#' }}"
                                    target="_blank" class="d-inline-flex align-items-center me-3">
                                    <i class="ti ti-file-text me-1"></i>{{ $file->original_name }}
                                </a>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
