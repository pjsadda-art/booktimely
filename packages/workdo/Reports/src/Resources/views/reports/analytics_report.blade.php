@extends('layouts.main')
@section('page-title')
    {{ __('Analytics Report') }}
@endsection

@section('page-breadcrumb')
    {{ __('Analytics Report') }}
@endsection
@section('page-action')
<div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="staffFilterDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            {{ __('Select Staff') }}
        </button>
        <div class="dropdown-menu p-3" aria-labelledby="staffFilterDropdown">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label" for="selectAll">
                    {{ __('Select All') }}
                </label>
            </div>
            @foreach ($staffs as $staff)
                <div class="form-check">
                    <input class="form-check-input staff-checkbox staff-select" type="checkbox"
                        value="{{ $staff->user_id }}" id="staff{{ $staff->user_id }}" onclick="filterTable()"
                        name="staff[]">
                    <label class="form-check-label" for="staff{{ $staff->user_id }}">
                        {{ $staff->name }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@section('content')
    <div class="app-table-wrp">
        <table class="table table-striped table-bordered appointment-table">
            <thead>
                <tr>
                    <th rowspan="2" scope="col">{{ __('Staff') }}</th>
                    <th rowspan="2" scope="col">{{ __('Appointment') }}</th>
                    <th colspan="{{ $statuses->count() }}" scope="col" class="text-center">{{ __('Status') }}</th>
                    <th rowspan="2" scope="col">{{ __('Revenue') }}</th>
                </tr>
                <tr>
                    @foreach ($statuses as $status)
                        <th scope="col">{{ $status->title }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="staffTableBody">
                @foreach ($staffs as $staff)
                    @php
                        $totalAppointmentsForStaff = isset($staffStatusAppointments[$staff->user_id])
                            ? $staffStatusAppointments[$staff->user_id]->sum('appointment_count')
                            : 0;

                        $totalRevenueForStaff = isset($staffStatusAppointments[$staff->user_id])
                            ? $staffStatusAppointments[$staff->user_id]->sum('total_revenue')
                            : 0;
                    @endphp
                    <tr data-staff-id="{{ $staff->user_id }}">
                        <td>{{ $staff->name }}</td>
                        <td>{{ $totalAppointmentsForStaff }}</td>
                        @foreach ($statuses as $status)
                            @php
                                $statusCount = isset($staffStatusAppointments[$staff->user_id][$status->id])
                                    ? $staffStatusAppointments[$staff->user_id][$status->id]->appointment_count
                                    : 0;
                            @endphp
                            <td>{{ $statusCount }}</td>
                        @endforeach
                        <td>{{ company_setting('defult_currancy_symbol') }}{{ number_format($totalRevenueForStaff, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>{{ __('Total :') }}</th>
                    <th id="totalAppointments">{{ $totalAppointments }}</th>
                    @foreach ($statuses as $status)
                        @php
                            $statusTotalCount = $staffStatusAppointments
                                ->flatMap(function ($appointments) use ($status) {
                                    return $appointments->where('appointment_status', $status->id);
                                })
                                ->sum('appointment_count');
                            if ($status->id === 0) {
                                $statusTotalCount += $staffStatusAppointments
                                    ->flatMap(function ($appointments) {
                                        return $appointments->where('appointment_status', 'Pending');
                                    })
                                    ->sum('appointment_count');
                            }
                        @endphp
                        <th class="status-total">{{ $statusTotalCount }}</th>
                    @endforeach
                    <th id="totalRevenue">
                        {{ company_setting('defult_currancy_symbol') }}{{ number_format($totalRevenue, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('input[name="staff[]"]').change(function() {
                filterTable();
            });

            $('#selectAll').change(function() {
                $('.staff-checkbox').prop('checked', $(this).prop('checked'));
                filterTable();
            });

            function filterTable() {
                const checkedBoxes = document.querySelectorAll('.staff-checkbox:checked');
                const staffIds = Array.from(checkedBoxes).map(cb => cb.value);

                $.ajax({
                    url: "{{ route('analytics.index') }}",
                    method: 'POST',
                    data: {
                        options: staffIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#staffTableBody').empty().html(response.html);
                        updateTotals();
                    }
                });
            }

            function updateTotals() {
                let totalAppointments = 0;
                let totalRevenue = 0;
                const statusTotals = {};

                const rows = document.querySelectorAll('#staffTableBody tr');
                rows.forEach(row => {
                    const appointments = parseInt(row.cells[1].innerText) || 0;
                    const revenue = parseFloat(row.cells[row.cells.length - 1].innerText.replace(/[^\d.-]/g,
                        '')) || 0;
                    totalAppointments += appointments;
                    totalRevenue += revenue;

                    row.querySelectorAll('td').forEach((cell, index) => {
                        if (index > 1 && index < row.cells.length - 1) {
                            const status = parseInt(cell.innerText) || 0;
                            statusTotals[index] = (statusTotals[index] || 0) + status;
                        }
                    });
                });

                document.getElementById('totalAppointments').innerText = totalAppointments;
                document.getElementById('totalRevenue').innerText =
                    '{{ company_setting('defult_currancy_symbol') }}' + totalRevenue.toFixed(2);
                document.querySelectorAll('.status-total').forEach((cell, index) => {
                    cell.innerText = statusTotals[index + 2] || 0;
                });
            }
        });
    </script>
@endpush
