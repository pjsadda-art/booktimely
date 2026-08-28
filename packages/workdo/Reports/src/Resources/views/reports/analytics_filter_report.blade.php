@foreach ($staffs as $staff)
    <tr data-staff-id="{{ $staff->id }}">
        <td>{{ $staff->name }}</td>
        <td>
            @php
                $totalAppointmentsForStaff = isset($staffStatusAppointments[$staff->user_id])
                    ? $staffStatusAppointments[$staff->user_id]->sum('appointment_count')
                    : 0;
                echo $totalAppointmentsForStaff;
            @endphp
        </td>
        @foreach ($statuses as $statusId => $statusTitle)
            <td>
                @php
                    $statusCount = isset($staffStatusAppointments[$staff->user_id][$statusId])
                        ? $staffStatusAppointments[$staff->user_id][$statusId]->appointment_count
                        : 0;
                    echo $statusCount;
                @endphp
            </td>
        @endforeach
        <td>
            @php
                $totalRevenueForStaff = isset($staffStatusAppointments[$staff->user_id])
                    ? $staffStatusAppointments[$staff->user_id]->sum('total_revenue')
                    : 0;
                echo company_setting('defult_currancy_symbol') . number_format($totalRevenueForStaff, 2);
            @endphp
        </td>
    </tr>
@endforeach
