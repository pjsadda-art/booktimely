@extends('layouts.main')

@section('page-title')
    {{ __('Staff Roster') }}
@endsection

@section('page-breadcrumb')
    {{ __('Staff Roster') }}
@endsection

@push('css')
    <style>
        .roster-table {
            width: 100%;
            border-collapse: collapse;
        }

        .roster-table th,
        .roster-table td {
            border: 1px solid #e5e5e5;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .roster-table th {
            background: var(--theme-color);
            color: #fff;
            font-weight: 600;
            border-color: var(--theme-color);
        }

        .roster-table td.staff-name {
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        .roster-cell {
            cursor: pointer;
            min-width: 90px;
        }

        .roster-cell:hover {
            background: #f1f5ff;
        }

        .roster-cell .shift-time {
            display: block;
            font-size: .8rem;
        }

        .roster-cell .shift-off {
            color: #999;
        }

        .roster-total-row td,
        .roster-total-col {
            font-weight: 700;
            background: #fff8e6;
            color: #8a6100;
        }

        .roster-total-row td {
            border-top: 2px solid var(--theme-color);
        }

        .roster-total-col {
            border-left: 2px solid var(--theme-color);
        }

        .roster-total-row td:first-child,
        .roster-total-row td:last-child {
            background: #ffedb8;
        }

        #roster-conflict-list {
            max-height: 240px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <select id="roster-location" class="form-select" style="max-width: 260px;" @if ($locations->count() === 1) disabled @endif>
                    @if ($locations->count() !== 1)
                        <option value="">{{ __('Select Location') }}</option>
                    @endif
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected($locations->count() === 1)>{{ $location->name }}</option>
                    @endforeach
                </select>

                <button type="button" class="btn btn-outline-secondary" id="roster-prev-week"><i class="ti ti-chevron-left"></i></button>
                <span id="roster-week-label" class="fw-semibold"></span>
                <button type="button" class="btn btn-outline-secondary" id="roster-next-week"><i class="ti ti-chevron-right"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="roster-today">{{ __('Today') }}</button>
            </div>

            <div id="roster-empty" class="text-muted d-none">{{ __('Select a location to view its staff roster.') }}</div>

            <div class="table-responsive">
                <table class="roster-table d-none" id="roster-table">
                    <thead>
                        <tr id="roster-header-row">
                            <th>{{ __('Staff') }}</th>
                        </tr>
                    </thead>
                    <tbody id="roster-body"></tbody>
                    <tfoot>
                        <tr class="roster-total-row" id="roster-total-row">
                            <td>{{ __('Total Hrs') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Shift edit modal -->
<div class="modal fade" id="roster-shift-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Shift') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="roster-shift-error" class="alert alert-danger d-none"></div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Shift Type') }}</label>
                    <select id="roster-shift-type" class="form-select">
                        <option value="continuous">{{ __('Continuous Shift') }}</option>
                        <option value="end_dated">{{ __('End-Dated Shift') }}</option>
                        <option value="casual">{{ __('Casual Shift') }}</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Start Time') }}</label>
                        <input type="time" id="roster-start-time" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('End Time') }}</label>
                        <input type="time" id="roster-end-time" class="form-control">
                    </div>
                </div>

                <div class="mb-3 roster-field-effective-from">
                    <label class="form-label">{{ __('Start Date') }}</label>
                    <input type="date" id="roster-effective-from" class="form-control">
                </div>

                <div class="mb-3 roster-field-effective-to d-none">
                    <label class="form-label">{{ __('End Date') }}</label>
                    <input type="date" id="roster-effective-to" class="form-control">
                </div>

                <div class="mb-3 roster-field-specific-date d-none">
                    <label class="form-label">{{ __('Date') }}</label>
                    <input type="date" id="roster-specific-date" class="form-control">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger d-none" id="roster-delete-btn">{{ __('Delete Shift') }}</button>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="roster-save-btn">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete mode / conflict modal -->
<div class="modal fade" id="roster-delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Shift') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="roster-delete-mode-group">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="roster-delete-mode" id="roster-delete-today" value="today_onward" checked>
                        <label class="form-check-label" for="roster-delete-today">{{ __('Delete from today onward') }}</label>
                    </div>
                    <div class="form-check mb-2" id="roster-delete-from-date-row">
                        <input class="form-check-input" type="radio" name="roster-delete-mode" id="roster-delete-from-date" value="from_date">
                        <label class="form-check-label" for="roster-delete-from-date">{{ __('Delete from a selected future date') }}</label>
                        <input type="date" id="roster-delete-from-date-input" class="form-control mt-1 d-none">
                    </div>
                    <div class="form-check mb-2 d-none" id="roster-delete-specific-row">
                        <input class="form-check-input" type="radio" name="roster-delete-mode" id="roster-delete-specific" value="specific_date">
                        <label class="form-check-label" for="roster-delete-specific">{{ __('Delete only this specific date') }}</label>
                    </div>
                </div>

                <div id="roster-conflict-warning" class="alert alert-warning d-none mt-3">
                    {{ __('This staff member has existing appointments during this shift period. Please reallocate all appointments before deleting this shift.') }}
                </div>
                <ul id="roster-conflict-list" class="list-group d-none mt-2"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="roster-confirm-delete-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var CFG = {
        gridUrl: @json(route('staff-roster.grid')),
        resolveUrl: @json(route('staff-roster.shifts.resolve')),
        storeUrl: @json(route('staff-roster.shifts.store')),
        updateUrlTpl: @json(route('staff-roster.shifts.update', ['id' => '__ID__'])),
        destroyUrlTpl: @json(route('staff-roster.shifts.destroy', ['id' => '__ID__'])),
    };

    var state = {
        locationId: '',
        weekStart: startOfWeek(new Date()),
        grid: null,
        activeCell: null, // {staffId, date}
        activeShift: null, // when editing an existing shift
    };

    function startOfWeek(date) {
        var d = new Date(date);
        d.setHours(0, 0, 0, 0);
        d.setDate(d.getDate() - d.getDay());
        return d;
    }

    function ymd(date) {
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    }

    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function jsonHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        };
    }

    function notify(message, type) {
        try {
            if (typeof toastrs === 'function' && document.getElementById('liveToast')) {
                toastrs(type === 'success' ? 'Success' : 'Error', message, type || 'success');
                return;
            }
        } catch (err) {}
        console.log(message);
    }

    function weekLabel() {
        var end = new Date(state.weekStart);
        end.setDate(end.getDate() + 6);
        return ymd(state.weekStart) + '  -  ' + ymd(end);
    }

    function loadGrid() {
        var el = document.getElementById('roster-table');
        var empty = document.getElementById('roster-empty');

        if (!state.locationId) {
            el.classList.add('d-none');
            empty.classList.remove('d-none');
            return;
        }

        empty.classList.add('d-none');
        document.getElementById('roster-week-label').textContent = weekLabel();

        var url = CFG.gridUrl + '?location_id=' + encodeURIComponent(state.locationId) + '&week_start=' + encodeURIComponent(ymd(state.weekStart));

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                state.grid = data;
                renderGrid(data);
                el.classList.remove('d-none');
            })
            .catch(function () { notify(@json(__('Failed to load roster.')), 'error'); });
    }

    function renderGrid(data) {
        var headerRow = document.getElementById('roster-header-row');
        headerRow.innerHTML = '<th>' + @json(__('Staff')) + '</th>';
        data.days.forEach(function (day) {
            var d = new Date(day + 'T00:00:00');
            headerRow.innerHTML += '<th>' + d.toLocaleDateString(undefined, { weekday: 'short' }) + '<br><small>' + day + '</small></th>';
        });
        headerRow.innerHTML += '<th>' + @json(__('Total Hrs')) + '</th>';

        var body = document.getElementById('roster-body');
        body.innerHTML = '';

        data.rows.forEach(function (row) {
            var tr = document.createElement('tr');
            var nameTd = document.createElement('td');
            nameTd.className = 'staff-name';
            nameTd.textContent = row.staff_name;
            tr.appendChild(nameTd);

            row.cells.forEach(function (cell) {
                var td = document.createElement('td');
                td.className = 'roster-cell';
                td.dataset.staffId = row.staff_id;
                td.dataset.date = cell.date;

                if (cell.hours) {
                    td.innerHTML = '<span class="shift-time">' + cell.hours.start + ' - ' + cell.hours.end + '</span><small>(' + cell.duration_hours + 'h)</small>';
                } else {
                    td.innerHTML = '<span class="shift-off">' + @json(__('OFF')) + '</span>';
                }

                td.addEventListener('click', function () { openShiftModal(row.staff_id, cell.date, cell); });
                tr.appendChild(td);
            });

            var totalTd = document.createElement('td');
            totalTd.className = 'roster-total-col';
            totalTd.textContent = row.total_hours + 'h';
            tr.appendChild(totalTd);

            body.appendChild(tr);
        });

        var totalRow = document.getElementById('roster-total-row');
        totalRow.innerHTML = '<td>' + @json(__('Total Hrs')) + '</td>';
        data.day_totals.forEach(function (t) {
            totalRow.innerHTML += '<td>' + t + '</td>';
        });
        totalRow.innerHTML += '<td>' + data.grand_total + '</td>';
    }

    function resetShiftModal() {
        document.getElementById('roster-shift-error').classList.add('d-none');
        document.getElementById('roster-shift-type').value = 'continuous';
        document.getElementById('roster-start-time').value = '';
        document.getElementById('roster-end-time').value = '';
        document.getElementById('roster-effective-from').value = '';
        document.getElementById('roster-effective-to').value = '';
        document.getElementById('roster-specific-date').value = '';
        toggleShiftTypeFields();
    }

    function toggleShiftTypeFields() {
        var type = document.getElementById('roster-shift-type').value;
        document.querySelector('.roster-field-effective-from').classList.toggle('d-none', type === 'casual');
        document.querySelector('.roster-field-effective-to').classList.toggle('d-none', type !== 'end_dated');
        document.querySelector('.roster-field-specific-date').classList.toggle('d-none', type !== 'casual');
    }

    document.getElementById('roster-shift-type').addEventListener('change', toggleShiftTypeFields);

    function openShiftModal(staffId, date, cell) {
        resetShiftModal();

        state.activeCell = { staffId: staffId, date: date };
        state.activeShift = null;

        var d = new Date(date + 'T00:00:00');
        document.getElementById('roster-effective-from').value = date;
        document.getElementById('roster-specific-date').value = date;

        if (cell && cell.hours) {
            document.getElementById('roster-start-time').value = cell.hours.start;
            document.getElementById('roster-end-time').value = cell.hours.end;
        }

        document.getElementById('roster-delete-btn').classList.toggle('d-none', !(cell && cell.hours));
        toggleShiftTypeFields();

        new bootstrap.Modal(document.getElementById('roster-shift-modal')).show();
    }

    document.getElementById('roster-save-btn').addEventListener('click', function () {
        if (!state.activeCell) return;

        var d = new Date(state.activeCell.date + 'T00:00:00');

        var payload = {
            staff_id: state.activeCell.staffId,
            location_id: state.locationId,
            shift_type: document.getElementById('roster-shift-type').value,
            start_time: document.getElementById('roster-start-time').value,
            end_time: document.getElementById('roster-end-time').value,
            weekday: d.getDay(),
            effective_from: document.getElementById('roster-effective-from').value,
            effective_to: document.getElementById('roster-effective-to').value,
            specific_date: document.getElementById('roster-specific-date').value,
        };

        fetch(CFG.storeUrl, {
            method: 'POST',
            headers: jsonHeaders(),
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (!res.ok) {
                    var err = document.getElementById('roster-shift-error');
                    err.textContent = res.data.error || @json(__('Failed to save shift.'));
                    err.classList.remove('d-none');
                    return;
                }

                bootstrap.Modal.getInstance(document.getElementById('roster-shift-modal')).hide();
                notify(@json(__('Shift saved.')), 'success');
                loadGrid();
            });
    });

    document.getElementById('roster-delete-btn').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('roster-shift-modal')).hide();

        var type = document.getElementById('roster-shift-type').value;
        document.getElementById('roster-delete-mode-group').classList.toggle('d-none', type === 'casual');
        document.getElementById('roster-delete-specific-row').classList.toggle('d-none', type !== 'casual');
        document.getElementById('roster-conflict-warning').classList.add('d-none');
        document.getElementById('roster-conflict-list').classList.add('d-none');
        document.getElementById('roster-conflict-list').innerHTML = '';

        new bootstrap.Modal(document.getElementById('roster-delete-modal')).show();
    });

    document.getElementById('roster-delete-from-date').addEventListener('change', function () {
        document.getElementById('roster-delete-from-date-input').classList.remove('d-none');
    });
    document.getElementById('roster-delete-today').addEventListener('change', function () {
        document.getElementById('roster-delete-from-date-input').classList.add('d-none');
    });

    document.getElementById('roster-confirm-delete-btn').addEventListener('click', function () {
        if (!state.activeCell) return;

        var type = document.getElementById('roster-shift-type').value;
        var mode = type === 'casual'
            ? 'specific_date'
            : (document.querySelector('input[name="roster-delete-mode"]:checked') || {}).value || 'today_onward';

        var fromDate = document.getElementById('roster-delete-from-date-input').value;

        fetch(CFG.resolveUrl + '?staff_id=' + encodeURIComponent(state.activeCell.staffId) + '&date=' + encodeURIComponent(state.activeCell.date), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (resolved) {
                if (!resolved || !resolved.id) {
                    notify(@json(__('No shift found for this cell.')), 'error');
                    return;
                }

                doDelete(resolved.id, mode, fromDate);
            });
    });

    function doDelete(shiftId, mode, fromDate) {
        var deleteUrl = CFG.destroyUrlTpl.replace('__ID__', shiftId);
        var body = { mode: mode };
        if (fromDate) body.from_date = fromDate;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: jsonHeaders(),
            body: JSON.stringify(body)
        })
            .then(function (r) { return r.json().then(function (data) { return { status: r.status, data: data }; }); })
            .then(function (res) {
                if (res.status === 409) {
                    document.getElementById('roster-conflict-warning').classList.remove('d-none');
                    var list = document.getElementById('roster-conflict-list');
                    list.classList.remove('d-none');
                    list.innerHTML = '';
                    (res.data.appointments || []).forEach(function (a) {
                        var li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.textContent = a.date + ' ' + a.time + ' — ' + (a.service || '') + ' — ' + (a.customer || '');
                        list.appendChild(li);
                    });
                    return;
                }

                if (res.status >= 400) {
                    notify(res.data.error || @json(__('Failed to delete shift.')), 'error');
                    return;
                }

                bootstrap.Modal.getInstance(document.getElementById('roster-delete-modal')).hide();
                notify(@json(__('Shift deleted.')), 'success');
                loadGrid();
            });
    }

    document.getElementById('roster-location').addEventListener('change', function (e) {
        state.locationId = e.target.value;
        loadGrid();
    });

    document.getElementById('roster-prev-week').addEventListener('click', function () {
        state.weekStart.setDate(state.weekStart.getDate() - 7);
        loadGrid();
    });
    document.getElementById('roster-next-week').addEventListener('click', function () {
        state.weekStart.setDate(state.weekStart.getDate() + 7);
        loadGrid();
    });
    document.getElementById('roster-today').addEventListener('click', function () {
        state.weekStart = startOfWeek(new Date());
        loadGrid();
    });

    document.getElementById('roster-week-label').textContent = weekLabel();

    // A business with only one location renders that <option> alone (no blank
    // placeholder), so it's selected by default with no "change" event firing —
    // load explicitly instead of waiting on one.
    state.locationId = document.getElementById('roster-location').value || '';
    loadGrid();
})();
</script>
@endpush
