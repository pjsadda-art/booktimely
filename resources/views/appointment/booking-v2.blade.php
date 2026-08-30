@extends('layouts.main')

@section('page-title')
    {{ __('Booking Calendar') }}
@endsection

@section('page-breadcrumb')
    {{ __('Booking Calendar') }}
@endsection

@section('page-action')
    <div class="d-flex col-auto gap-2">
        @stack('addButtonHook')
        @permission('appointment create')
            <a href="#" class="btn btn-sm btn-primary" id="bv2-toolbar-new-appt"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"><i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/resource-timegrid@6.1.10/index.global.min.css" rel="stylesheet">
    <style>
        /* ================= calendar ================= */
        .bv2-page {
            transition: margin-right .3s cubic-bezier(.4, 0, .2, 1);
        }

        .bv2-calendar-wrap {
            min-width: 0;
        }

        /* Darkens the whole grid on days no staff is rostered (so "nothing
           bookable today" reads as a distinct state from a normal working day
           at a glance) — an overlay div rather than overriding FullCalendar's
           own internal classes, so it survives a FullCalendar version bump. */
        .bv2-calendar-inner {
            position: relative;
        }

        .bv2-day-off-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .14);
            pointer-events: none;
            z-index: 2;
            display: none;
        }

        .bv2-calendar-wrap.bv2-day-off .bv2-day-off-overlay {
            display: block;
        }

        /* dense time grid — the compact look comes from here */
        .fc .fc-timegrid-slot {
            height: 8px !important;
        }

        .fc-event-time,
        .fc-event-title {
            font-size: .72rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fc-event,
        .fc-event:not([href]) {
            padding: unset;
            cursor: pointer;
        }

        /* Same dark tint and non-interactive behaviour as .bv2-day-off-overlay
           above, so "outside this staff member's hours" (a working day) and
           "nobody rostered at all" (a closed day) read as one consistent
           pattern rather than two different shades of not-bookable. */
        .fc .fc-non-business {
            background: rgba(15, 23, 42, .28);
            color: #999;
            cursor: not-allowed;
            pointer-events: none;
        }

        .fc .fc-non-business:hover {
            background: rgba(15, 23, 42, .28);
        }

        .fc-timegrid-event-short .fc-event-main-frame {
            overflow: visible;
        }

        /* block-time chips (inert until a block_times source exists) */
        .bv2-block-event {
            background: #111827 !important;
            border-color: #000 !important;
        }

        .bv2-block-inner {
            display: flex;
            align-items: flex-start;
            gap: 4px;
            padding: 1px 3px;
            overflow: hidden;
        }

        .bv2-block-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .bv2-block-text span {
            font-size: .72rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bv2-block-reason {
            opacity: .75;
        }

        .bv2-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
        }

        .bv2-current-date {
            font-weight: 600;
            min-width: 190px;
        }

        /* ================= slide panel shell ================= */
        #bv2-panel {
            position: fixed;
            top: 0;
            right: -470px;
            width: 450px;
            height: 100vh;
            background: #fff;
            /* Below Bootstrap's backdrop (1050) and modal (1055) so the New
               Customer modal opens in front of the panel, not behind it. */
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 28px rgba(0, 0, 0, .14);
            border-left: 3px solid #4f46e5;
            transition: right .3s cubic-bezier(.4, 0, .2, 1);
        }

        #bv2-panel.open {
            right: 0;
        }

        .bv2-panel-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 0 0 auto;
        }

        .bv2-panel-header h6 {
            margin: 0;
            font-weight: 700;
            color: #fff;
        }

        .bv2-panel-close {
            background: none;
            border: 0;
            color: #fff;
            font-size: 1.1rem;
            line-height: 1;
            opacity: .9;
        }

        .bv2-panel-close:hover {
            opacity: 1;
        }

        .bv2-panel-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem;
        }

        .bv2-panel-footer {
            flex: 0 0 auto;
            background: #f8f9fa;
            border-top: 1px solid #e5e7eb;
            padding: .75rem 1rem;
            display: flex;
            gap: .5rem;
        }

        /* ≥1400px the panel pushes the page; below it overlays */
        @media (min-width: 1400px) {
            .bv2-page.bv2-pushed {
                margin-right: 450px;
            }
        }

        @media (max-width: 768px) {
            #bv2-panel {
                width: 100%;
                right: -100%;
            }

            .bv2-calendar-wrap {
                overflow-x: auto;
            }

            .bv2-calendar-wrap .fc {
                min-width: 700px;
            }

            .bv2-current-date {
                min-width: 0;
            }
        }

        /* ================= panel content ================= */
        .bv2-row {
            display: flex;
            gap: .5rem;
            align-items: baseline;
            padding: .45rem 0;
            border-bottom: 1px solid #f1f1f4;
        }

        .bv2-row-label {
            color: #6b7280;
            font-size: .78rem;
            min-width: 88px;
            flex: 0 0 88px;
        }

        .bv2-row-value {
            font-size: .85rem;
            font-weight: 500;
            word-break: break-word;
        }

        .bv2-section-label {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .7rem;
            color: #6b7280;
            margin: 1rem 0 .25rem;
        }

        .bv2-muted-line {
            color: #6b7280;
            font-size: .78rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .bv2-badge {
            display: inline-block;
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .7rem;
            margin-right: .25rem;
        }

        .bv2-badge-ghost {
            background: #f3f4f6;
            color: #9ca3af;
            border: 1px solid #e5e7eb;
        }

        .bv2-table {
            width: 100%;
            font-size: .78rem;
        }

        .bv2-table th {
            color: #6b7280;
            font-weight: 600;
        }

        .bv2-table th,
        .bv2-table td {
            padding: .3rem .25rem;
            border-bottom: 1px solid #f1f1f4;
        }

        /* ================= create / edit cards ================= */
        .bv2-card {
            background: #f8f9ff;
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            margin-bottom: .85rem;
            overflow: hidden;
        }

        .bv2-card-header {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
            font-size: .8rem;
            padding: .5rem .75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bv2-card-remove {
            background: none;
            border: 0;
            color: #6b7280;
            font-size: .75rem;
        }

        .bv2-card-remove:hover {
            color: #dc2626;
        }

        .bv2-card-body {
            padding: .75rem;
        }

        .bv2-field {
            margin-bottom: .6rem;
        }

        .bv2-field label {
            display: block;
            font-size: .75rem;
            color: #6b7280;
            margin-bottom: .15rem;
        }

        .bv2-req {
            color: #dc2626;
        }

        /* service chips — one click = one instance, repeats allowed */
        .bv2-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            margin-bottom: .4rem;
        }

        .bv2-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 16px;
            padding: .2rem .6rem;
            font-size: .75rem;
        }

        .bv2-chip-ord {
            background: #c7d2fe;
            border-radius: 8px;
            padding: 0 .3rem;
            font-size: .65rem;
            font-weight: 600;
        }

        /* inline per-booking duration / price overrides */
        .bv2-chip-num {
            width: 44px;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 0 .2rem;
            font-size: .7rem;
            background: #fff;
            text-align: right;
        }

        .bv2-chip-num.bv2-chip-price {
            width: 58px;
        }

        /* hide the number spinners so the chip stays compact */
        .bv2-chip-num::-webkit-outer-spin-button,
        .bv2-chip-num::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .bv2-chip-num {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .bv2-chip-x {
            cursor: pointer;
            opacity: .55;
            font-weight: 700;
        }

        .bv2-chip-x:hover {
            color: #dc2626;
            opacity: 1;
        }

        .bv2-summary {
            font-size: .75rem;
            color: #4f46e5;
            margin-bottom: .4rem;
        }

        .bv2-service-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            padding: .25rem 0;
            border-bottom: 1px solid #eef2ff;
        }

        .bv2-service-row .bv2-chip-x {
            color: #dc2626;
        }

        .bv2-readonly {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: .3rem .5rem;
            font-size: .8rem;
        }

        /* customer typeahead */
        .bv2-typeahead {
            position: relative;
        }

        .bv2-ta-results {
            position: absolute;
            z-index: 5;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .1);
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .bv2-ta-results.show {
            display: block;
        }

        .bv2-ta-item {
            padding: .4rem .6rem;
            cursor: pointer;
            border-bottom: 1px solid #f6f6f8;
        }

        .bv2-ta-item:hover {
            background: #f8f9ff;
        }

        .bv2-ta-name {
            font-weight: 600;
            font-size: .82rem;
        }

        .bv2-ta-meta {
            color: #6b7280;
            font-size: .72rem;
        }

        .bv2-customer-card {
            display: flex;
            gap: .6rem;
            align-items: center;
            background: #f8f9ff;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            padding: .5rem .65rem;
        }

        .bv2-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
        }

        /* WhatsApp Chat tab */
        .bv2-wa-list {
            max-height: 55vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            padding: .25rem;
        }

        .bv2-wa-bubble {
            max-width: 80%;
            border-radius: 10px;
            padding: .4rem .6rem;
            font-size: .85rem;
        }

        .bv2-wa-in {
            align-self: flex-start;
            background: #f3f4f6;
        }

        .bv2-wa-out {
            align-self: flex-end;
            background: #e0e7ff;
        }

        .bv2-wa-text {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .bv2-wa-meta {
            font-size: .7rem;
            color: #6b7280;
            margin-top: .15rem;
            text-align: right;
        }
    </style>
@endpush

@section('content')
    <div class="bv2-page" id="bv2-page">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="bv2-toolbar">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="bv2-prev"
                                    title="{{ __('Previous day') }}"><i class="ti ti-chevron-left"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="bv2-today">
                                    {{ __('Today') }}</button>
                                <button type="button" class="btn btn-outline-secondary" id="bv2-next"
                                    title="{{ __('Next day') }}"><i class="ti ti-chevron-right"></i></button>
                            </div>

                            <input type="date" class="form-control form-control-sm" id="bv2-date" style="width:auto;">

                            <span class="bv2-current-date" id="bv2-title"></span>

                            <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                                <select class="form-control form-control-sm" id="bv2-location" style="width:auto;">
                                    <option value="">{{ __('All Locations') }}</option>
                                    @foreach ($locations as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ (string) $selectedLocation === (string) $id ? 'selected' : '' }}>
                                            {{ $name }}</option>
                                    @endforeach
                                </select>

                                <select class="form-control form-control-sm" id="bv2-staff-mode" style="width:auto;">
                                    <option value="roster">{{ __('Rostered Staff') }}</option>
                                    <option value="all_appointments">{{ __('Staff With Appointments') }}</option>
                                    @foreach ($staffOptions as $staffId => $staffName)
                                        <option value="{{ $staffId }}">{{ $staffName }}</option>
                                    @endforeach
                                </select>

                                <button type="button" class="btn btn-sm btn-outline-secondary" id="bv2-refresh"
                                    title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-2">
                        <div class="bv2-calendar-wrap" id="bv2-calendar-wrap">
                            <div class="alert alert-secondary py-2 px-3 mb-2 d-none" id="bv2-day-off-banner">
                                <i class="ti ti-lock me-1"></i><span id="bv2-day-off-banner-text"></span>
                            </div>
                            <div class="bv2-calendar-inner">
                                <div id="bv2-calendar"></div>
                                <div class="bv2-day-off-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- One panel, three modes: view / create / edit. Body and footer are
         re-rendered per mode so no stale handlers survive a switch. --}}
    <aside id="bv2-panel" aria-hidden="true">
        <div class="bv2-panel-header">
            <h6 id="bv2-panel-title">{{ __('Appointment') }}</h6>
            <button type="button" class="bv2-panel-close" id="bv2-panel-close"
                aria-label="{{ __('Close') }}"><i class="ti ti-x"></i></button>
        </div>
        <div class="bv2-panel-body" id="bv2-panel-body"></div>
        <div class="bv2-panel-footer" id="bv2-panel-footer"></div>
    </aside>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.10/index.global.min.js"></script>
    <script type="text/javascript">
        (function () {
            'use strict';

            var CFG = {
                eventsUrl: "{{ route('bookings-v2.events') }}",
                staffsUrl: "{{ route('bookings-v2.staffs') }}",
                formDataUrl: "{{ route('bookings-v2.form-data') }}",
                customersUrl: "{{ route('bookings-v2.customers') }}",
                customerHistoryUrl: "{{ route('bookings-v2.customer.history', ['id' => '__ID__']) }}",
                detailUrl: "{{ route('bookings-v2.appointment', ['id' => '__ID__']) }}",
                storeUrl: "{{ route('bookings-v2.appointment.store') }}",
                updateUrl: "{{ route('bookings-v2.appointment.update', ['id' => '__ID__']) }}",
                moveUrl: "{{ route('bookings-v2.appointment.move', ['id' => '__ID__']) }}",
                statusUrl: "{{ route('appointment.status.update', ['id' => '__ID__']) }}",
                newCustomerUrl: "{{ route('customer.ajax.create') }}",
                jobCardStoreUrl: "{{ route('job-card.store', ['appointment' => '__ID__']) }}",
                jobCardFileDeleteUrl: "{{ route('job-card.file.destroy', ['id' => '__ID__']) }}",
                proposalPrefillUrl: "{{ route('bookings-v2.proposal-prefill', ['e_id' => '__ID__']) }}",
                license: @json($licenseKey),
                locale: "{{ app()->getLocale() }}",
                currency: @json($currencySymbol),
                locations: @json($locations),
                weekStart: {{ (int) $weekStartDay }},
                slotInterval: {{ (int) $slotInterval }},
                bufferBefore: {{ (int) $calendarBufferBefore }},
                bufferAfter: {{ (int) $calendarBufferAfter }},
                canCreate: {{ auth()->user()->isAbleTo('appointment create') ? 'true' : 'false' }},
                canEdit: {{ auth()->user()->isAbleTo('appointment edit') ? 'true' : 'false' }},
                canManageDeposit: {{ auth()->user()->isAbleTo('deposit manage') ? 'true' : 'false' }}
            };

            var T = {
                mismatch: @json(__('Some appointments belong to staff who are not rostered today — their columns are shown as unavailable.')),
                dayOff: @json(__('No staff rostered — nothing bookable today.')),
                moveConfirm: @json(__('Move this appointment to')),
                moveFailed: @json(__('Could not move the appointment.')),
                statusFailed: @json(__('Could not update the status.')),
                loadFailed: @json(__('Could not load the appointment.')),
                outside: @json(__('Outside business hours')),
                outsideHours: @json(__('That time is outside this staff member\'s working hours.')),
                loading: @json(__('Loading…')),
                saving: @json(__('Saving…')),
                saved: @json(__('Appointment saved.')),
                saveFailed: @json(__('Could not save the appointment.')),
                needCustomer: @json(__('Please choose a customer.')),
                newCustomerNeedName: @json(__('Please enter the customer\'s name.')),
                newCustomerNeedMobile: @json(__('Please enter a mobile number.')),
                newCustomerSaved: @json(__('Customer created.')),
                newCustomerFailed: @json(__('Could not create the customer.')),
                needLocation: @json(__('Please choose a location.')),
                needStaff: @json(__('Please choose a staff member for every card.')),
                needService: @json(__('Please add at least one service to every card.')),
                needTime: @json(__('Please set a start time for every card.')),
                checkedOut: @json(__('Appointment already checked out')),
                jobCardEmpty: @json(__('No job card attached yet.')),
                jobCardHelp: @json(__('JPG, PNG or PDF, up to 10MB each.')),
                jobCardUploaded: @json(__('Job card uploaded.')),
                jobCardUploadFailed: @json(__('Could not upload the job card.')),
                jobCardDeleted: @json(__('Job card file deleted.')),
                jobCardDeleteFailed: @json(__('Could not delete the job card file.')),
                jobCardDeleteConfirm: @json(__('Delete this job card file?')),
                waEmpty: @json(__('No WhatsApp messages yet. Start the conversation by sending a message.')),
                waNoMobile: @json(__('Customer does not have a valid WhatsApp-compatible mobile number.')),
                waNotConfigured: @json(__('WhatsApp is not configured for this account.')),
                waPlaceholder: @json(__('Type a message…')),
                waSend: @json(__('Send'))
            };

            var calendar = null;
            var currentInterval = CFG.slotInterval;
            var mismatchHandled = {};
            // businessHours per resource, used to guard slot selection
            var resourceHours = {};

            var formData = null;      // staff / services / statuses, fetched once per open
            var panelMode = null;     // 'view' | 'create' | 'edit'
            var viewData = null;      // last detail blob
            var draft = null;         // create/edit working state

            var viewTab = 'details';       // 'details' | 'whatsapp' — the view panel's tab strip
            var whatsappMessages = [];      // chat thread for the currently open appointment
            var whatsappPollTimer = null;   // cleared whenever the chat tab isn't the visible one

            /* ===================== helpers ===================== */

            function esc(value) {
                return String(value === null || value === undefined ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            /**
             * Open one of the platform's standard ajax modals.
             *
             * The global handler in custom.js is delegated on `document`, so it
             * cannot be invoked directly — it needs a real click on a real
             * element that is actually in the DOM for the event to bubble.
             * Hence the temporary anchor: it reuses the platform's own loader,
             * including common_bind() and validation(), rather than
             * reimplementing modal loading here and drifting from it.
             */
            function openModal(url, title, size) {
                var $trigger = $('<a>', {
                    href: '#',
                    'data-ajax-popup': 'true',
                    'data-url': url,
                    'data-title': title,
                    'data-size': size || 'md'
                }).css('display', 'none').appendTo('body');

                $trigger.trigger('click');
                $trigger.remove();
            }

            function pad2(n) {
                n = String(n);
                return n.length < 2 ? '0' + n : n;
            }

            // Local date accessors, never toISOString(): the latter shifts the
            // day for anyone west of UTC on the date boundary.
            function localYmd(date) {
                return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
            }

            function localDmy(date) {
                return pad2(date.getDate()) + '-' + pad2(date.getMonth() + 1) + '-' + date.getFullYear();
            }

            function localHm(date) {
                return pad2(date.getHours()) + ':' + pad2(date.getMinutes());
            }

            function dmyToYmd(dmy) {
                var p = String(dmy).split('-');
                return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
            }

            function ymdToDmy(ymd) {
                var p = String(ymd).split('-');
                return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
            }

            function toMinutes(hhmm) {
                var p = String(hhmm).split(':');
                return (parseInt(p[0], 10) || 0) * 60 + (parseInt(p[1], 10) || 0);
            }

            function fromMinutes(total) {
                total = Math.max(0, Math.min(total, (24 * 60) - 1));
                return pad2(Math.floor(total / 60)) + ':' + pad2(total % 60);
            }

            function hhmmss(minutes) {
                return pad2(Math.floor(minutes / 60)) + ':' + pad2(minutes % 60) + ':00';
            }

            // "Now", rounded up to the grid's own slot interval — a sensible
            // default start time for the header "+" button, which has no
            // clicked slot to take one from.
            function nowRoundedToSlot() {
                var now = new Date();
                var interval = CFG.slotInterval || 30;
                var minutes = now.getHours() * 60 + now.getMinutes();
                var rounded = Math.ceil(minutes / interval) * interval;
                return fromMinutes(rounded % (24 * 60));
            }

            function shiftClock(hhmm, delta) {
                return hhmmss(Math.max(0, Math.min((24 * 60) - 1, toMinutes(hhmm) + delta)));
            }

            function money(amount) {
                return CFG.currency + (Math.round((Number(amount) || 0) * 100) / 100).toFixed(2);
            }

            function durationLabel(minutes) {
                minutes = Number(minutes) || 0;
                var h = Math.floor(minutes / 60);
                var m = minutes % 60;
                return (h ? h + 'h ' : '') + (m || !h ? m + 'min' : '').trim();
            }

            function csrf() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }

            // toastrs() only knows 'success' vs everything-else, and needs the
            // layout's #liveToast element; fall back to the console rather than
            // letting a missing toast break the calling flow.
            function notify(message, type) {
                try {
                    if (typeof toastrs === 'function' && document.getElementById('liveToast')) {
                        toastrs(type === 'success' ? 'Success' : 'Error', message, type || 'success');
                        return;
                    }
                } catch (err) {
                    /* fall through to the console */
                }

                console.log(message);
            }

            function selectedLocation() {
                return document.getElementById('bv2-location').value || '';
            }

            // The one location a single-location business has — there is no real
            // choice to make, so the create panel should never ask for it.
            function soleLocationId() {
                var ids = Object.keys(CFG.locations);
                return ids.length === 1 ? ids[0] : null;
            }

            function selectedStaffMode() {
                return document.getElementById('bv2-staff-mode').value || 'roster';
            }

            function jsonHeaders() {
                return {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                };
            }

            /* ===================== feeds ===================== */

            function fetchResources(info, onSuccess, onFailure) {
                var date = localYmd(info.start);
                var url = CFG.staffsUrl + '?date=' + encodeURIComponent(date) +
                    '&location_id=' + encodeURIComponent(selectedLocation()) +
                    '&staff_id=' + encodeURIComponent(selectedStaffMode());

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) {
                        var mismatch = response.headers.get('X-Staff-Mismatch') === '1';

                        // Somebody has an appointment today but no roster column.
                        // The server already appended them as an off-roster column,
                        // so this is purely informational — once per date.
                        if (mismatch && selectedStaffMode() === 'roster' && !mismatchHandled[date]) {
                            mismatchHandled[date] = true;
                            notify(T.mismatch, 'warning');
                        }

                        return response.json();
                    })
                    .then(function (resources) {
                        resourceHours = {};
                        (resources || []).forEach(function (resource) {
                            resourceHours[String(resource.id)] = resource.businessHours || false;
                        });

                        applyTimeWindow(resources);
                        onSuccess(resources);
                    })
                    .catch(onFailure);
            }

            function fetchEvents(info, onSuccess, onFailure) {
                // staff_id is forwarded so isolating one column also narrows the
                // query; the server ignores the non-numeric roster modes.
                var url = CFG.eventsUrl + '?date=' + encodeURIComponent(localYmd(info.start)) +
                    '&location_id=' + encodeURIComponent(selectedLocation()) +
                    '&staff_id=' + encodeURIComponent(selectedStaffMode());

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) {
                        // The business's slot granularity rides along on every
                        // response, so it can change without a page reload.
                        var interval = parseInt(response.headers.get('X-Slot-Interval') || '0', 10);

                        if (interval >= 5 && interval !== currentInterval) {
                            currentInterval = interval;
                            applySlotInterval(interval);
                        }

                        return response.json();
                    })
                    .then(function (events) {
                        // Naive timestamps can be mis-detected as all-day and then
                        // render into a slot that allDaySlot:false has hidden.
                        events.forEach(function (event) {
                            event.allDay = false;
                        });
                        onSuccess(events);
                    })
                    .catch(onFailure);
            }

            function applySlotInterval(interval) {
                calendar.setOption('slotDuration', hhmmss(interval));
                calendar.setOption('snapDuration', hhmmss(interval));
                calendar.setOption('slotLabelInterval', hhmmss(interval < 15 ? interval * 2 : interval));
            }

            // Collapse the visible span to the staff's actual working day.
            function applyTimeWindow(resources) {
                var min = null;
                var max = null;

                (resources || []).forEach(function (resource) {
                    if (!resource.businessHours || !resource.businessHours.length) {
                        return;
                    }

                    resource.businessHours.forEach(function (window) {
                        if (min === null || window.startTime < min) {
                            min = window.startTime;
                        }
                        if (max === null || window.endTime > max) {
                            max = window.endTime;
                        }
                    });
                });

                setDayOff(min === null || max === null);

                if (min === null || max === null) {
                    return; // keep the 07:00–23:00 fallback
                }

                calendar.setOption('slotMinTime', shiftClock(min, -CFG.bufferBefore));
                calendar.setOption('slotMaxTime', shiftClock(max, CFG.bufferAfter));
                calendar.setOption('scrollTime', shiftClock(min, -CFG.bufferBefore));
            }

            // No resource has any working hours for the viewed day — nobody is
            // rostered, so nothing is bookable regardless of why (closed for
            // business, or just an unstaffed gap). Darkens the grid and posts a
            // banner so that reads as a distinct state from a normal work day.
            function setDayOff(isOff) {
                var wrap = document.getElementById('bv2-calendar-wrap');
                var banner = document.getElementById('bv2-day-off-banner');
                var bannerText = document.getElementById('bv2-day-off-banner-text');

                wrap.classList.toggle('bv2-day-off', isOff);
                banner.classList.toggle('d-none', !isOff);

                if (isOff) {
                    bannerText.textContent = T.dayOff;
                }
            }

            function ensureFormData(date) {
                if (formData) {
                    return Promise.resolve(formData);
                }

                var url = CFG.formDataUrl + '?date=' + encodeURIComponent(date) +
                    '&location_id=' + encodeURIComponent(selectedLocation());

                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        formData = data;
                        return data;
                    });
            }

            /* ===================== panel shell ===================== */

            function openPanel(title) {
                var panel = document.getElementById('bv2-panel');

                document.getElementById('bv2-panel-title').textContent = title || '';
                panel.classList.add('open');
                panel.setAttribute('aria-hidden', 'false');
                document.getElementById('bv2-page').classList.add('bv2-pushed');

                afterPanelTransition();
            }

            function closePanel() {
                var panel = document.getElementById('bv2-panel');

                panel.classList.remove('open');
                panel.setAttribute('aria-hidden', 'true');
                document.getElementById('bv2-page').classList.remove('bv2-pushed');

                panelMode = null;
                draft = null;
                stopWhatsappPolling();
                viewTab = 'details';

                afterPanelTransition();
            }

            // The one sustained interval in this file — kept tightly scoped to
            // "chat tab visible and panel open" so it can never outlive either.
            function stopWhatsappPolling() {
                if (whatsappPollTimer) {
                    clearInterval(whatsappPollTimer);
                    whatsappPollTimer = null;
                }
            }

            // The push/unpush is a CSS transition, so the calendar must reflow
            // once it has finished or it keeps a stale width.
            function afterPanelTransition() {
                setTimeout(function () {
                    if (calendar) {
                        calendar.updateSize();
                    }
                    fitHeight();
                }, 320);
            }

            function setBody(html) {
                document.getElementById('bv2-panel-body').innerHTML = html;
                document.getElementById('bv2-panel-body').scrollTop = 0;
            }

            /**
             * Rebuild the footer from scratch on every mode switch — reusing the
             * container without replacing it is how stale handlers from the
             * previous mode survive and fire on the wrong record.
             *
             * @param buttons [{label, className, onClick, disabled, title}]
             */
            function setFooter(buttons) {
                var footer = document.getElementById('bv2-panel-footer');
                footer.innerHTML = '';

                (buttons || []).forEach(function (spec) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = spec.className || 'btn btn-sm btn-light';
                    button.textContent = spec.label;

                    if (spec.disabled) {
                        button.disabled = true;
                    }
                    if (spec.title) {
                        button.setAttribute('title', spec.title);
                    }
                    if (spec.onClick) {
                        button.addEventListener('click', spec.onClick);
                    }

                    footer.appendChild(button);
                });
            }

            function fieldRow(label, value) {
                if (value === null || value === undefined || value === '') {
                    return '';
                }

                return '<div class="bv2-row"><div class="bv2-row-label">' + esc(label) + '</div>' +
                    '<div class="bv2-row-value">' + esc(value) + '</div></div>';
            }

            // Tinted pill derived from the status colour — readable on white.
            function statusPill(label, color) {
                color = color || '#6b7280';

                return '<span class="bv2-badge" style="background:' + esc(color) + '22;color:' + esc(color) +
                    ';border:1px solid ' + esc(color) + '44">' + esc(label) + '</span>';
            }

            /* ===================== view mode ===================== */

            function openView(id) {
                panelMode = 'view';
                viewTab = 'details';
                stopWhatsappPolling();
                setBody('<div class="text-muted small">' + esc(T.loading) + '</div>');
                setFooter([]);
                openPanel("{{ __('Appointment Details') }}");

                fetch(CFG.detailUrl.replace('__ID__', id), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('http ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        viewData = data;
                        renderView(data);
                    })
                    .catch(function () {
                        setBody('<div class="text-danger small">' + esc(T.loadFailed) + '</div>');
                    });
            }

            // Entry point the fetch in openView() calls. Dispatches to whichever
            // tab is active — the "Details" tab (unchanged, below) is the only
            // one that existed before the WhatsApp Chat tab, so it stays the
            // default and the fallback whenever the chat tab isn't available.
            function renderView(data) {
                if (viewTab === 'whatsapp' && data.whatsapp && data.whatsapp.visible) {
                    renderWhatsAppChat(data);
                    return;
                }

                stopWhatsappPolling();
                renderViewDetails(data);
            }

            // Shared by both tabs so switching tabs re-renders through the same
            // two entry points rather than duplicating the strip's markup.
            function whatsappTabStripHtml(data) {
                if (!data.whatsapp || !data.whatsapp.visible) {
                    return '';
                }

                return '<div class="bv2-tabs d-flex gap-1 mb-2">' +
                    '<button type="button" class="btn btn-sm ' + (viewTab === 'details' ? 'btn-primary' : 'btn-outline-secondary') +
                    '" data-act="view-tab" data-tab="details">' + "{{ __('Details') }}" + '</button>' +
                    '<button type="button" class="btn btn-sm ' + (viewTab === 'whatsapp' ? 'btn-primary' : 'btn-outline-secondary') +
                    '" data-act="view-tab" data-tab="whatsapp">' + "{{ __('WhatsApp Chat') }}" + '</button>' +
                    '</div>';
            }

            function bindViewTabs(data) {
                document.querySelectorAll('[data-act="view-tab"]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var tab = button.getAttribute('data-tab');

                        if (tab === viewTab) {
                            return;
                        }

                        viewTab = tab;

                        if (tab !== 'whatsapp') {
                            stopWhatsappPolling();
                        }

                        renderView(data);
                    });
                });
            }

            // Wired after every renderViewDetails() render (uploads/deletes
            // reload the whole panel via openView(), same as changeStatus()
            // does, so the job card list can never drift from the server).
            function bindJobCard(data) {
                var uploadBtn = document.getElementById('bv2-job-card-upload-btn');
                var fileInput = document.getElementById('bv2-job-card-upload');

                if (uploadBtn && fileInput) {
                    uploadBtn.addEventListener('click', function () {
                        if (!fileInput.files.length) {
                            return;
                        }

                        var body = new FormData();
                        for (var i = 0; i < fileInput.files.length; i++) {
                            body.append('files[]', fileInput.files[i]);
                        }

                        uploadBtn.disabled = true;

                        fetch(CFG.jobCardStoreUrl.replace('__ID__', data.id), {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() },
                            body: body
                        })
                            .then(function (response) {
                                return response.json().then(function (json) { return { ok: response.ok, json: json }; });
                            })
                            .then(function (res) {
                                if (!res.ok) {
                                    notify((res.json && res.json.error) || T.jobCardUploadFailed, 'error');
                                    return;
                                }
                                notify(T.jobCardUploaded, 'success');
                                openView(data.id);
                            })
                            .catch(function () {
                                notify(T.jobCardUploadFailed, 'error');
                            })
                            .finally(function () {
                                uploadBtn.disabled = false;
                            });
                    });
                }

                var list = document.getElementById('bv2-job-card-files');
                if (list) {
                    list.addEventListener('click', function (e) {
                        var btn = e.target.closest('[data-job-card-delete]');
                        if (!btn) {
                            return;
                        }
                        if (!window.confirm(T.jobCardDeleteConfirm)) {
                            return;
                        }

                        fetch(CFG.jobCardFileDeleteUrl.replace('__ID__', btn.getAttribute('data-job-card-delete')), {
                            method: 'DELETE',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() }
                        })
                            .then(function (response) {
                                return response.json().then(function (json) { return { ok: response.ok, json: json }; });
                            })
                            .then(function (res) {
                                if (!res.ok) {
                                    notify((res.json && res.json.error) || T.jobCardDeleteFailed, 'error');
                                    return;
                                }
                                notify(T.jobCardDeleted, 'success');
                                openView(data.id);
                            })
                            .catch(function () {
                                notify(T.jobCardDeleteFailed, 'error');
                            });
                    });
                }
            }

            function renderViewDetails(data) {
                var customer = data.customer || {};
                var notify_ = String(customer.notification || '');
                var smsOn = notify_ === 'both' || notify_ === 'sms';
                var emailOn = notify_ === 'both' || notify_ === 'email';
                var html = whatsappTabStripHtml(data);

                // 1. customer
                html += '<div class="bv2-row" style="border-bottom:0;padding-bottom:.1rem">' +
                    '<div class="bv2-row-value" style="font-weight:700;font-size:.95rem">' +
                    esc(customer.name) + '</div></div>';

                if (customer.mobile) {
                    html += '<div class="bv2-muted-line"><i class="ti ti-phone"></i>' + esc(customer.mobile) + '</div>';
                }
                if (customer.email) {
                    html += '<div class="bv2-muted-line"><i class="ti ti-mail"></i>' + esc(customer.email) + '</div>';
                }

                // 2. notify badges — filled when the channel is reachable
                html += '<div style="margin:.5rem 0">' +
                    '<span class="bv2-badge ' + (smsOn ? 'bg-primary text-white' : 'bv2-badge-ghost') + '">SMS</span>' +
                    '<span class="bv2-badge ' + (emailOn ? 'bg-info text-white' : 'bv2-badge-ghost') + '">Email</span>' +
                    '</div>';

                // 3. date / time / location
                html += fieldRow("{{ __('Date') }}", data.date);
                html += fieldRow("{{ __('Time') }}", data.time);
                html += fieldRow("{{ __('Location') }}", (data.location || {}).name);

                // 4. total
                html += '<div class="bv2-row"><div class="bv2-row-label">{{ __('Total') }}</div>' +
                    '<div class="bv2-row-value text-success fw-bold">' + esc(money(data.total_amount)) + '</div></div>';

                // 5. inline status change
                html += '<div class="bv2-row"><div class="bv2-row-label">{{ __('Status') }}</div>' +
                    '<div class="bv2-row-value d-flex gap-1 align-items-center">';

                if (CFG.canEdit) {
                    html += '<select class="form-control form-control-sm" id="bv2-status-select" style="width:auto">';
                    Object.keys(data.statuses || {}).forEach(function (statusId) {
                        html += '<option value="' + esc(statusId) + '"' +
                            (String(statusId) === String(data.status_id) ? ' selected' : '') + '>' +
                            esc(data.statuses[statusId]) + '</option>';
                    });
                    html += '</select><button type="button" class="btn btn-sm btn-primary" id="bv2-status-apply">' +
                        '<i class="ti ti-check"></i></button>';
                } else {
                    html += esc((data.statuses || {})[data.status_id] || '-');
                }

                html += '</div></div>';

                // 6. invoice
                html += '<div class="bv2-row"><div class="bv2-row-label">{{ __('Invoice') }}</div><div class="bv2-row-value">';
                if (data.invoice_id && data.invoice_url) {
                    html += '<a class="bv2-badge bg-success text-white" href="' + esc(data.invoice_url) + '">' +
                        "{{ __('Invoice exists') }}" + '</a>';
                } else {
                    html += '<span class="bv2-badge bv2-badge-ghost">' + "{{ __('No invoice yet') }}" + '</span>';
                }
                html += '</div></div>';

                // 6b. deposit
                var deposit = data.deposit || {};
                if (deposit.status && deposit.status !== 'none') {
                    var depositClasses = {
                        pending: 'bg-warning text-dark',
                        paid: 'bg-success text-white',
                        forfeited: 'bg-dark text-white',
                        refunded: 'bg-info text-white'
                    };
                    var depositLabel = deposit.status.charAt(0).toUpperCase() + deposit.status.slice(1);
                    var depositText = depositLabel;
                    if (deposit.amount !== null && deposit.amount !== undefined) {
                        depositText += ' · ' + CFG.currency + Number(deposit.amount).toFixed(2);
                    }

                    html += '<div class="bv2-row"><div class="bv2-row-label">{{ __('Deposit') }}</div>' +
                        '<div class="bv2-row-value"><span class="bv2-badge ' +
                        (depositClasses[deposit.status] || 'bv2-badge-ghost') + '">' +
                        esc(depositText) + '</span></div></div>';
                }

                // 7. notes
                html += fieldRow("{{ __('Notes') }}", data.notes);

                // 7b. business-defined fields — REGO / Make & Model on a
                // car-service tenant. Definition order, so it reads consistently
                // even when a value is blank.
                var customFields = data.custom_fields || {};
                var definitions = data.custom_field_definitions || [];
                var customRows = '';

                definitions.forEach(function (field) {
                    customRows += fieldRow(field.label, customFields[field.label]);
                });

                // Anything stored under a label that no longer has a definition
                // would otherwise be invisible.
                Object.keys(customFields).forEach(function (label) {
                    var known = definitions.some(function (field) {
                        return field.label === label;
                    });

                    if (!known) {
                        customRows += fieldRow(label, customFields[label]);
                    }
                });

                if (customRows) {
                    html += '<div class="bv2-section-label">' + "{{ __('Details') }}" + '</div><hr class="mt-0">' +
                        customRows;
                }

                // 8. services table
                if (data.group && data.group.length) {
                    html += '<div class="bv2-section-label">{{ __('Services') }}</div><hr class="mt-0">';
                    html += '<table class="bv2-table"><thead><tr>' +
                        '<th>{{ __('Service') }}</th><th>{{ __('Staff') }}</th><th>{{ __('Time') }}</th>' +
                        '<th>{{ __('Dur') }}</th><th class="text-end">{{ __('Price') }}</th></tr></thead><tbody>';

                    data.group.forEach(function (row) {
                        html += '<tr><td>' + esc(row.service) + '</td><td>' + esc(row.staff) + '</td>' +
                            '<td>' + esc(row.time) + '</td><td>' + esc(row.duration || '-') + '</td>' +
                            '<td class="text-end">' + esc(money(row.price)) + '</td></tr>';
                    });

                    html += '</tbody><tfoot><tr><td colspan="4" class="text-end fw-bold">{{ __('Total') }}</td>' +
                        '<td class="text-end fw-bold">' + esc(money(data.total_amount)) + '</td></tr></tfoot></table>';
                }

                // 8b. job card — scanned paper job card, attached per appointment.
                // Auto Repair-specific: the server omits job_card_enabled for every
                // other industry, so the section (and its listeners in bindJobCard)
                // simply doesn't exist in the DOM for them.
                if (data.job_card_enabled) {
                    html += '<div class="bv2-section-label">' + "{{ __('Job Card') }}" + '</div><hr class="mt-0">';

                    var jobCardFiles = data.job_card_files || [];
                    html += '<div id="bv2-job-card-files">';
                    if (jobCardFiles.length) {
                        jobCardFiles.forEach(function (file) {
                            html += '<div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1" data-job-card-file-row="' + file.id + '">' +
                                '<a href="' + esc(file.url || '#') + '" target="_blank" class="text-truncate me-2">' +
                                '<i class="ti ti-file-text me-1"></i>' + esc(file.name) + '</a>' +
                                (CFG.canEdit
                                    ? '<button type="button" class="btn btn-sm btn-outline-danger" data-job-card-delete="' + file.id + '"><i class="ti ti-trash"></i></button>'
                                    : '') +
                                '</div>';
                        });
                    } else {
                        html += '<p class="text-muted small mb-0" id="bv2-job-card-empty">' + esc(T.jobCardEmpty) + '</p>';
                    }
                    html += '</div>';

                    if (CFG.canEdit) {
                        html += '<div class="input-group input-group-sm mb-1">' +
                            '<input type="file" class="form-control" id="bv2-job-card-upload" multiple accept=".jpg,.jpeg,.png,.pdf">' +
                            '<button type="button" class="btn btn-outline-primary" id="bv2-job-card-upload-btn">' +
                            '<i class="ti ti-upload me-1"></i>' + "{{ __('Upload') }}" + '</button></div>' +
                            '<small class="text-muted d-block mb-2">' + esc(T.jobCardHelp) + '</small>';
                    }
                }

                // 9. history — meaningless for a walk-in with no customer record
                if (!customer.is_walkin) {
                    html += historyTable("{{ __('Previous Appointments') }}", data.previous);
                    html += historyTable("{{ __('Upcoming Appointments') }}", data.upcoming);
                }

                // 10. sms history
                if (data.sms_history && data.sms_history.length) {
                    html += '<div class="bv2-section-label">{{ __('SMS History') }}</div><hr class="mt-0">';
                    html += '<table class="bv2-table"><tbody>';
                    data.sms_history.forEach(function (row) {
                        html += '<tr><td>' + esc(row.sent_at) + '</td><td>' + esc(row.action) + '</td>' +
                            '<td>' + esc(row.mobile) + '</td><td><span class="bv2-badge ' +
                            (row.status === 'sent' ? 'bg-success' : 'bg-secondary') + ' text-white">' +
                            esc(row.status) + '</span></td></tr>';
                    });
                    html += '</tbody></table>';
                }

                setBody(html);
                bindViewTabs(data);
                bindJobCard(data);

                var apply = document.getElementById('bv2-status-apply');
                if (apply) {
                    apply.addEventListener('click', function () {
                        changeStatus(data.id, document.getElementById('bv2-status-select').value);
                    });
                }

                // Footer: Edit stays visible but disabled once checked out, so the
                // reason is legible instead of the button just vanishing.
                var buttons = [];
                var checkedOut = !!data.invoice_id;

                if (CFG.canEdit) {
                    buttons.push({
                        label: "{{ __('Edit') }}",
                        className: 'btn btn-sm btn-warning',
                        disabled: checkedOut,
                        title: checkedOut ? T.checkedOut : '',
                        onClick: openEdit
                    });
                }

                // Deposit actions. Both gates come from the server so this panel,
                // the appointment listing and the details modal cannot disagree
                // about the same booking.
                if (CFG.canManageDeposit && deposit.status === 'none') {
                    buttons.push({
                        label: "{{ __('Request Deposit') }}",
                        className: 'btn btn-sm btn-warning',
                        // Disabled rather than absent, with the server's reason as
                        // the tooltip — either "no gateway configured" or "the
                        // invoice is already paid".
                        disabled: !deposit.can_request,
                        title: deposit.can_request ? '' : (deposit.can_request_reason || ''),
                        onClick: function () { openModal(deposit.request_url, "{{ __('Request Deposit Payment') }}"); }
                    });
                }

                if (CFG.canManageDeposit && deposit.can_take_payment &&
                    (deposit.status === 'none' || deposit.status === 'pending')) {
                    // Never gated on a gateway: this is the counter path.
                    buttons.push({
                        label: "{{ __('Manual Deposit') }}",
                        className: 'btn btn-sm btn-success',
                        onClick: function () { openModal(deposit.payment_url, "{{ __('Manual Deposit') }}"); }
                    });
                }

                if (checkedOut && data.invoice_url) {
                    buttons.push({
                        label: "{{ __('View Invoice') }}",
                        className: 'btn btn-sm btn-success',
                        onClick: function () { window.location.href = data.invoice_url; }
                    });
                } else if (data.checkout_url) {
                    buttons.push({
                        label: "{{ __('Checkout') }}",
                        className: 'btn btn-sm btn-success',
                        onClick: function () { window.location.href = data.checkout_url; }
                    });
                }

                // Independent of the balance invoice above — a deposit taken on the
                // spot is mirrored into its own invoice as soon as it's collected,
                // whether or not the booking has been checked out yet.
                if (data.deposit_invoice_url) {
                    buttons.push({
                        label: "{{ __('View Deposit Invoice') }}",
                        className: 'btn btn-sm btn-outline-success',
                        onClick: function () { window.location.href = data.deposit_invoice_url; }
                    });
                }

                buttons.push({
                    label: "{{ __('Close') }}",
                    className: 'btn btn-sm btn-light ms-auto',
                    onClick: closePanel
                });

                setFooter(buttons);
            }

            /* ===================== WhatsApp Chat tab ===================== */

            function renderWhatsAppChat(data) {
                var whatsapp = data.whatsapp || {};
                var html = whatsappTabStripHtml(data);
                var canChat = whatsapp.has_valid_mobile && whatsapp.configured;

                html += '<div class="bv2-whatsapp">';

                if (!whatsapp.has_valid_mobile) {
                    // FR7
                    html += '<div class="alert alert-warning small mb-0">' +
                        esc(T.waNoMobile) + '</div>';
                } else if (!whatsapp.configured) {
                    // FR8
                    html += '<div class="alert alert-danger small mb-0">' +
                        esc(T.waNotConfigured) + '</div>';
                } else {
                    html += '<div class="bv2-wa-list" id="bv2-wa-list"><div class="text-muted small">' +
                        esc(T.loading) + '</div></div>';
                    html += '<div class="d-flex gap-1 mt-2">' +
                        '<textarea class="form-control form-control-sm" id="bv2-wa-input" rows="2" ' +
                        'placeholder="' + esc(T.waPlaceholder) + '"></textarea>' +
                        '<button type="button" class="btn btn-sm btn-primary align-self-end" id="bv2-wa-send">' +
                        esc(T.waSend) + '</button></div>';
                }

                html += '</div>';

                setBody(html);
                bindViewTabs(data);

                setFooter([{
                    label: "{{ __('Close') }}",
                    className: 'btn btn-sm btn-light ms-auto',
                    onClick: closePanel
                }]);

                stopWhatsappPolling();

                if (!canChat) {
                    return;
                }

                loadWhatsappMessages(data);
                // Only while this tab is the one showing — closePanel() and
                // switching tabs both clear this, so it can't run in the
                // background after the panel is gone.
                whatsappPollTimer = setInterval(function () { loadWhatsappMessages(data); }, 4000);

                var sendButton = document.getElementById('bv2-wa-send');
                if (sendButton) {
                    sendButton.addEventListener('click', function () { sendWhatsappMessage(data); });
                }
            }

            function loadWhatsappMessages(data) {
                fetch(data.whatsapp.messages_url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('http ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        whatsappMessages = payload.messages || [];
                        renderWhatsappList();
                    })
                    .catch(function () {
                        // Transient fetch failure (e.g. a slow network tick) — leave
                        // the last successfully loaded thread on screen rather than
                        // replacing it with an error on every missed poll (NFR6).
                    });
            }

            function renderWhatsappList() {
                var list = document.getElementById('bv2-wa-list');

                if (!list) {
                    return;   // the tab was switched away while this poll was in flight
                }

                if (!whatsappMessages.length) {
                    list.innerHTML = '<div class="text-muted small">' + esc(T.waEmpty) + '</div>';
                    return;
                }

                var html = '';

                whatsappMessages.forEach(function (message) {
                    var mine = message.sender_type === 'staff';
                    var statusIcon = '';

                    if (mine) {
                        statusIcon = message.status === 'read' ? '<i class="ti ti-checks text-info"></i>'
                            : message.status === 'delivered' ? '<i class="ti ti-checks"></i>'
                            : message.status === 'failed' ? '<i class="ti ti-alert-circle text-danger"></i>'
                            : '<i class="ti ti-check"></i>';
                    }

                    html += '<div class="bv2-wa-bubble ' + (mine ? 'bv2-wa-out' : 'bv2-wa-in') + '">' +
                        '<div class="bv2-wa-text">' + esc(message.message_content) + '</div>' +
                        '<div class="bv2-wa-meta">' + esc(formatWaTimestamp(message.created_at)) +
                        (statusIcon ? ' ' + statusIcon : '') + '</div></div>';
                });

                list.innerHTML = html;
                list.scrollTop = list.scrollHeight;
            }

            function sendWhatsappMessage(data) {
                var input = document.getElementById('bv2-wa-input');
                var text = (input.value || '').trim();

                if (!text) {
                    return;
                }

                var sendButton = document.getElementById('bv2-wa-send');
                sendButton.disabled = true;
                input.disabled = true;

                // Optimistic append, reconciled once the response returns — so a
                // message is never silently missing from the thread while the
                // request is in flight.
                var optimistic = {
                    sender_type: 'staff',
                    direction: 'outbound',
                    message_type: 'text',
                    message_content: text,
                    status: 'queued',
                    created_at: new Date().toISOString()
                };
                whatsappMessages.push(optimistic);
                renderWhatsappList();
                input.value = '';

                fetch(data.whatsapp.send_url, {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ message: text })
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        var index = whatsappMessages.indexOf(optimistic);

                        if (result.ok && result.payload.success && result.payload.message) {
                            if (index !== -1) {
                                whatsappMessages[index] = result.payload.message;
                            }
                        } else {
                            if (index !== -1) {
                                optimistic.status = 'failed';
                            }
                            notify((result.payload && result.payload.error) || T.saveFailed, 'error');
                        }

                        renderWhatsappList();
                        sendButton.disabled = false;
                        input.disabled = false;
                        input.focus();
                    })
                    .catch(function () {
                        var index = whatsappMessages.indexOf(optimistic);

                        if (index !== -1) {
                            optimistic.status = 'failed';
                        }

                        renderWhatsappList();
                        notify(T.saveFailed, 'error');
                        sendButton.disabled = false;
                        input.disabled = false;
                    });
            }

            function formatWaTimestamp(value) {
                if (!value) {
                    return '';
                }

                try {
                    var date = new Date(value);

                    return date.toLocaleDateString() + ' ' +
                        date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                } catch (err) {
                    return '';
                }
            }

            function historyTable(title, rows) {
                if (!rows || !rows.length) {
                    return '';
                }

                var html = '<div class="bv2-section-label">' + esc(title) + '</div><hr class="mt-0">' +
                    '<table class="bv2-table"><tbody>';

                rows.forEach(function (row) {
                    html += '<tr><td>' + esc(row.date) + '</td><td>' + esc(row.service) + '</td>' +
                        '<td class="text-end">' + statusPill(row.status, row.color) + '</td></tr>';
                });

                return html + '</tbody></table>';
            }

            /* ===================== create mode ===================== */

            function openCreate(prefill) {
                if (!CFG.canCreate) {
                    return;
                }

                panelMode = 'create';
                // One booking, one staff member. Multiple services still stack
                // sequentially within it.
                draft = {
                    locationId: selectedLocation() || soleLocationId() || '',
                    // A quotation "Convert to Appointment" hands over a
                    // customer straight away (same {id,name,mobile,email}
                    // shape the customer typeahead itself builds); nothing
                    // else that opens this panel does.
                    customer: prefill.customer || null,
                    customFields: {},
                    staffId: prefill.staffId ? String(prefill.staffId) : '',
                    date: prefill.date,
                    startTime: prefill.time,
                    statusId: '',
                    notes: '',
                    services: []
                };

                setBody('<div class="text-muted small">' + esc(T.loading) + '</div>');
                setFooter([]);
                openPanel("{{ __('New Appointment') }}");

                ensureFormData(dmyToYmd(prefill.date)).then(function () {
                    if (!draft.statusId) {
                        draft.statusId = formData.default_status || '';
                    }

                    // Same shape addService's own push() builds — one entry
                    // per unit, repeated ids stack (e.g. qty 2 of the same
                    // service), and anything that doesn't resolve to a real
                    // bookable Service (a Parts/product line on the
                    // quotation) is silently skipped rather than blocking
                    // the whole prefill.
                    (prefill.serviceIds || []).forEach(function (serviceId) {
                        var service = findService(serviceId);
                        if (service) {
                            draft.services.push({
                                id: service.id,
                                name: service.name,
                                duration: service.duration,
                                price: service.price
                            });
                        }
                    });

                    renderCreate();
                }).catch(function () {
                    setBody('<div class="text-danger small">' + esc(T.loadFailed) + '</div>');
                });
            }

            function totalDuration() {
                return draft.services.reduce(function (sum, service) {
                    return sum + (Number(service.duration) || 0);
                }, 0);
            }

            function totalPrice() {
                return draft.services.reduce(function (sum, service) {
                    return sum + (Number(service.price) || 0);
                }, 0);
            }

            function endTime() {
                return fromMinutes(toMinutes(draft.startTime) + totalDuration());
            }

            function serviceOptionsHtml() {
                var html = '<option value="">' + "{{ __('Add a service…') }}" + '</option>';

                (formData.services || []).forEach(function (group) {
                    html += '<optgroup label="' + esc(group.category) + '">';
                    group.items.forEach(function (item) {
                        html += '<option value="' + esc(item.id) + '">' + esc(item.name) +
                            ' (' + esc(item.duration) + 'min – ' + esc(money(item.price)) + ')</option>';
                    });
                    html += '</optgroup>';
                });

                return html;
            }

            function staffOptionsHtml(selectedId) {
                var html = '<option value="">' + "{{ __('Select staff') }}" + '</option>';
                var found = false;

                (formData.staff || []).forEach(function (member) {
                    var selected = String(member.id) === String(selectedId);
                    if (selected) {
                        found = true;
                    }
                    html += '<option value="' + esc(member.id) + '"' + (selected ? ' selected' : '') + '>' +
                        esc(member.title) + '</option>';
                });

                // Preselect fallback: a staff member who is not in today's list
                // would otherwise be silently dropped from the form.
                if (selectedId && !found) {
                    html += '<option value="' + esc(selectedId) + '" selected>' +
                        "{{ __('Staff') }}" + ' #' + esc(selectedId) + '</option>';
                }

                return html;
            }

            function statusOptionsHtml(selectedId) {
                var html = '';

                (formData.statuses || []).forEach(function (status) {
                    html += '<option value="' + esc(status.id) + '"' +
                        (String(status.id) === String(selectedId) ? ' selected' : '') + '>' +
                        esc(status.title) + '</option>';
                });

                return html;
            }

            // Chips are keyed by position, not service id: removing one repeat of
            // a service must not remove its siblings.
            function chipsHtml() {
                if (!draft.services.length) {
                    return '<div class="text-muted small mb-1">' + "{{ __('No services yet.') }}" + '</div>';
                }

                var seen = {};
                var counts = {};

                draft.services.forEach(function (service) {
                    counts[service.id] = (counts[service.id] || 0) + 1;
                });

                var html = '<div class="bv2-chips">';

                draft.services.forEach(function (service, index) {
                    seen[service.id] = (seen[service.id] || 0) + 1;

                    var ordinal = counts[service.id] > 1
                        ? '<span class="bv2-chip-ord">#' + seen[service.id] + '</span>'
                        : '';

                    // Duration and price are editable per booking; blank falls
                    // back to the service catalogue on the server.
                    html += '<span class="bv2-chip">' + ordinal + esc(service.name) +
                        overrideInputsHtml(service, index) +
                        '<span class="bv2-chip-x" data-index="' + index +
                        '" data-act="rm-service">&times;</span></span>';
                });

                return html + '</div>';
            }

            // Shared by the create chips and the edit rows.
            function overrideInputsHtml(service, index) {
                return '<input type="number" class="bv2-chip-num" data-act="svc-duration" data-index="' +
                    index + '" min="1" max="1440" value="' +
                    esc(service.duration) + '" title="' + "{{ __('Duration (minutes)') }}" + '">' +
                    '<span class="text-muted">min</span>' +
                    '<input type="number" class="bv2-chip-num bv2-chip-price" data-act="svc-price" data-index="' +
                    index + '" min="0" step="0.01" value="' +
                    esc(service.price) + '" title="' + "{{ __('Price') }}" + '">';
            }

            // Business-defined fields (REGO, Make & Model, …). One set per
            // booking — every row the save creates stores the same values.
            function customFieldsHtml() {
                if (!formData || !formData.custom_fields_enabled || !(formData.custom_fields || []).length) {
                    return '';
                }

                // No rule under the label here: this group sits inside the form,
                // not as a standalone section like it does in the detail view.
                var html = '<div class="bv2-section-label">' + "{{ __('Details') }}" + '</div>';

                formData.custom_fields.forEach(function (field) {
                    var value = draft.customFields[field.label];

                    if (value === undefined || value === null) {
                        value = field.value || '';
                    }

                    html += '<div class="bv2-field"><label>' + esc(field.label) + '</label>';

                    if (field.type === 'textarea') {
                        html += '<textarea class="form-control form-control-sm" rows="2" ' +
                            'data-act="custom-field" data-label="' + esc(field.label) + '">' +
                            esc(value) + '</textarea>';
                    } else {
                        var inputType = field.type === 'date' ? 'date'
                            : (field.type === 'number' ? 'number' : 'text');

                        html += '<input type="' + inputType + '" class="form-control form-control-sm" ' +
                            'data-act="custom-field" data-label="' + esc(field.label) + '" value="' +
                            esc(value) + '">';
                    }

                    html += '</div>';
                });

                return html;
            }

            function renderCreate() {
                var html = '<div class="bv2-card"><div class="bv2-card-header">' +
                    '<span>' + "{{ __('Appointment') }}" + '</span></div><div class="bv2-card-body">';

                html += '<div class="bv2-field"><label>' + "{{ __('Customer') }}" +
                    ' <span class="bv2-req">*</span></label>';

                if (draft.customer) {
                    html += '<div class="bv2-customer-card">' +
                        '<div class="bv2-avatar"><i class="ti ti-user"></i></div><div>' +
                        '<div class="bv2-ta-name">' + esc(draft.customer.name) + '</div>' +
                        '<div class="bv2-ta-meta">' +
                        esc([draft.customer.mobile, draft.customer.email].filter(Boolean).join(' · ')) +
                        '</div></div>' +
                        '<button type="button" class="bv2-card-remove ms-auto" data-act="clear-customer">' +
                        "{{ __('Change') }}" + '</button></div>';
                } else {
                    html += '<div class="d-flex gap-1"><div class="bv2-typeahead flex-fill">' +
                        '<input type="text" class="form-control form-control-sm" id="bv2-cust-input" ' +
                        'placeholder="' + "{{ __('Search name, mobile or email') }}" + '" autocomplete="off">' +
                        '<div class="bv2-ta-results" id="bv2-ta-results"></div></div>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary" data-act="new-customer" ' +
                        'title="' + "{{ __('New Customer') }}" + '"><i class="ti ti-plus"></i></button></div>';
                }

                html += '</div>';

                // Location comes from the toolbar and is display-only — except on
                // "All Locations", where it has to be chosen here or the booking
                // has nothing to save against. A single-location business has no
                // real choice either way, so it is always display-only.
                html += '<div class="bv2-field"><label>' + "{{ __('Location') }}" +
                    ' <span class="bv2-req">*</span></label>';

                var fixedLocationId = selectedLocation() || soleLocationId();

                if (fixedLocationId) {
                    html += '<div class="bv2-readonly">' +
                        esc(CFG.locations[fixedLocationId] || fixedLocationId) + '</div>';
                } else {
                    html += '<select class="form-control form-control-sm" data-act="location">' +
                        '<option value="">' + "{{ __('Select location') }}" + '</option>';

                    Object.keys(CFG.locations).forEach(function (locationId) {
                        html += '<option value="' + esc(locationId) + '"' +
                            (String(locationId) === String(draft.locationId) ? ' selected' : '') + '>' +
                            esc(CFG.locations[locationId]) + '</option>';
                    });

                    html += '</select>';
                }

                html += '</div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Staff') }}" +
                    ' <span class="bv2-req">*</span></label>' +
                    '<select class="form-control form-control-sm" data-act="staff">' +
                    staffOptionsHtml(draft.staffId) + '</select></div>';

                html += '<div class="d-flex gap-2"><div class="bv2-field flex-fill"><label>' +
                    "{{ __('Date') }}" + ' <span class="bv2-req">*</span></label>' +
                    '<input type="date" class="form-control form-control-sm" data-act="date" value="' +
                    esc(dmyToYmd(draft.date)) + '"></div>' +
                    '<div class="bv2-field flex-fill"><label>' + "{{ __('Start Time') }}" +
                    ' <span class="bv2-req">*</span></label>' +
                    '<input type="time" class="form-control form-control-sm" data-act="time" value="' +
                    esc(draft.startTime) + '"></div></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Services') }}" +
                    ' <span class="bv2-req">*</span></label>' + chipsHtml() +
                    '<div class="bv2-summary"><i class="ti ti-clock"></i> ' +
                    esc(durationLabel(totalDuration())) + ' &nbsp;|&nbsp; ' +
                    esc(money(totalPrice())) + ' &nbsp;|&nbsp; ' +
                    esc(draft.startTime) + '–' + esc(endTime()) + '</div>' +
                    '<div class="d-flex gap-1"><select class="form-control form-control-sm" ' +
                    'data-act="service-pick">' + serviceOptionsHtml() + '</select>' +
                    '<button type="button" class="btn btn-sm btn-primary" data-act="add-service">' +
                    '<i class="ti ti-plus"></i></button></div></div>';

                html += customFieldsHtml();

                html += '<div class="bv2-field"><label>' + "{{ __('Notes') }}" + '</label>' +
                    '<textarea class="form-control form-control-sm" rows="2" data-act="notes">' +
                    esc(draft.notes) + '</textarea></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Status') }}" + '</label>' +
                    '<select class="form-control form-control-sm" data-act="status">' +
                    statusOptionsHtml(draft.statusId) + '</select></div>';

                html += '</div></div>';

                if (draft.customer) {
                    html += '<div id="bv2-cust-history"></div>';
                }

                setBody(html);
                bindDraftEvents();

                setFooter([
                    {
                        label: "{{ __('Save Appointment') }}",
                        className: 'btn btn-sm btn-primary flex-fill',
                        onClick: saveCreate
                    },
                    {
                        label: "{{ __('Cancel') }}",
                        className: 'btn btn-sm btn-light',
                        onClick: closePanel
                    }
                ]);
            }

            /**
             * The "+" next to the customer search — same card styling as the rest
             * of this panel instead of the old customer-management Bootstrap
             * modal, and it drops the created customer straight into draft.customer
             * instead of leaving the appointment form to search for them again.
             */
            function renderNewCustomerCard() {
                var html = '<div class="bv2-card"><div class="bv2-card-header">' +
                    '<span>' + "{{ __('New Customer') }}" + '</span></div><div class="bv2-card-body">';

                html += '<div class="bv2-field"><label>' + "{{ __('Name') }}" +
                    ' <span class="bv2-req">*</span></label>' +
                    '<input type="text" class="form-control form-control-sm" id="bv2-nc-name" autofocus></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Mobile No') }}" +
                    ' <span class="bv2-req">*</span></label>' +
                    '<input type="text" class="form-control form-control-sm" id="bv2-nc-mobile" placeholder="04xxxxxxxx"></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Email') }}" + '</label>' +
                    '<input type="email" class="form-control form-control-sm" id="bv2-nc-email"></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Gender') }}" + '</label>' +
                    '<select class="form-control form-control-sm" id="bv2-nc-gender">' +
                    '<option value="male">' + "{{ __('Male') }}" + '</option>' +
                    '<option value="female">' + "{{ __('Female') }}" + '</option></select></div>';

                // Same rule as Customer::isValidAustralianMobile()/isValidEmail() —
                // a toggle only turns on once its matching contact detail actually
                // looks valid, so staff see at a glance whether what they just
                // typed will actually be reachable.
                html += '<div class="bv2-field"><div class="form-check form-switch">' +
                    '<input type="checkbox" class="form-check-input" id="bv2-nc-sms" disabled>' +
                    '<label class="form-check-label" for="bv2-nc-sms">' + "{{ __('SMS Communication') }}" + '</label>' +
                    '</div><small class="text-muted d-block" id="bv2-nc-sms-hint">' +
                    "{{ __('Turns on once a valid mobile number is entered.') }}" + '</small></div>';

                html += '<div class="bv2-field"><div class="form-check form-switch">' +
                    '<input type="checkbox" class="form-check-input" id="bv2-nc-email-comm" disabled>' +
                    '<label class="form-check-label" for="bv2-nc-email-comm">' + "{{ __('Email Communication') }}" + '</label>' +
                    '</div><small class="text-muted d-block" id="bv2-nc-email-comm-hint">' +
                    "{{ __('Turns on once a valid email is entered.') }}" + '</small></div>';

                html += '</div></div>';

                setBody(html);
                bindNewCustomerValidation();

                setFooter([
                    {
                        label: "{{ __('Create Customer') }}",
                        className: 'btn btn-sm btn-primary flex-fill',
                        onClick: saveNewCustomer
                    },
                    {
                        label: "{{ __('Back') }}",
                        className: 'btn btn-sm btn-light',
                        onClick: renderCreate
                    }
                ]);

                var nameInput = document.getElementById('bv2-nc-name');
                if (nameInput) {
                    nameInput.focus();
                }
            }

            function looksLikeAuMobile(raw) {
                var cleaned = (raw || '').replace(/[^0-9+]/g, '');
                if (cleaned.indexOf('00') === 0) { cleaned = '+' + cleaned.slice(2); }
                if (/^61\d{9}$/.test(cleaned)) { cleaned = '+' + cleaned; }
                return /^(?:\+614|04)\d{8}$/.test(cleaned);
            }

            function looksLikeEmail(raw) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((raw || '').trim());
            }

            function bindNewCustomerValidation() {
                var mobileInput = document.getElementById('bv2-nc-mobile');
                var smsToggle = document.getElementById('bv2-nc-sms');
                var emailInput = document.getElementById('bv2-nc-email');
                var emailToggle = document.getElementById('bv2-nc-email-comm');

                function sync(input, toggle, check) {
                    var ok = check(input.value);
                    toggle.disabled = !ok;
                    toggle.checked = ok;
                }

                mobileInput.addEventListener('input', function () { sync(mobileInput, smsToggle, looksLikeAuMobile); });
                emailInput.addEventListener('input', function () { sync(emailInput, emailToggle, looksLikeEmail); });
            }

            function saveNewCustomer() {
                var name = document.getElementById('bv2-nc-name').value.trim();
                var mobile = document.getElementById('bv2-nc-mobile').value.trim();
                var email = document.getElementById('bv2-nc-email').value.trim();
                var gender = document.getElementById('bv2-nc-gender').value;

                if (!name) {
                    notify(T.newCustomerNeedName, 'error');
                    return;
                }
                if (!mobile) {
                    notify(T.newCustomerNeedMobile, 'error');
                    return;
                }

                lockFooter(T.saving);

                var body = new FormData();
                body.append('name', name);
                body.append('mobile_no', mobile);
                body.append('email', email);
                body.append('gender', gender);
                body.append('communication_sms', document.getElementById('bv2-nc-sms').checked ? '1' : '0');
                body.append('communication_email', document.getElementById('bv2-nc-email-comm').checked ? '1' : '0');

                fetch(CFG.newCustomerUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() },
                    body: body
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (!data.status) {
                            throw new Error(data.message || T.newCustomerFailed);
                        }

                        draft.customer = {
                            id: data.customer.user_id || data.customer.id,
                            name: data.customer.name,
                            mobile: data.customer.mobile,
                            email: data.customer.email
                        };

                        notify(data.message || T.newCustomerSaved, 'success');
                        renderCreate();
                        loadCustomerHistory();
                    })
                    .catch(function (error) {
                        notify(error.message || T.newCustomerFailed, 'error');
                        renderNewCustomerCard();
                    });
            }

            /* ===================== edit mode ===================== */

            function openEdit() {
                if (!viewData || !CFG.canEdit) {
                    return;
                }

                panelMode = 'edit';

                var first = (viewData.group && viewData.group[0]) || {};
                var times = String(viewData.time || '').split('-');

                draft = {
                    id: viewData.id,
                    date: viewData.date,
                    startTime: (times[0] || '').trim(),
                    statusId: viewData.status_id,
                    notes: viewData.notes || '',
                    customer: viewData.customer || {},
                    // Prefill with what was actually stored on this appointment.
                    customFields: Object.assign({}, viewData.custom_fields || {}),
                    staffId: String(first.staff_id || ''),
                    services: (viewData.group || []).map(function (row) {
                        return {
                            id: row.service_id,
                            name: row.service,
                            duration: Number(row.duration) || 0,
                            price: Number(row.price) || 0
                        };
                    })
                };

                setBody('<div class="text-muted small">' + esc(T.loading) + '</div>');
                setFooter([]);
                document.getElementById('bv2-panel-title').textContent = "{{ __('Edit Appointment') }}";

                ensureFormData(dmyToYmd(draft.date)).then(renderEdit).catch(function () {
                    setBody('<div class="text-danger small">' + esc(T.loadFailed) + '</div>');
                });
            }

            function renderEdit() {
                var customer = draft.customer || {};
                var html = '';

                // Customer is read-only in edit mode.
                html += '<div class="bv2-customer-card mb-3">' +
                    '<div class="bv2-avatar"><i class="ti ti-user"></i></div><div>' +
                    '<div class="bv2-ta-name">' + esc(customer.name) + '</div>' +
                    '<div class="bv2-ta-meta">' +
                    esc([customer.mobile, customer.email].filter(Boolean).join(' · ')) +
                    '</div></div></div>';

                // Shared fields apply to the whole booking.
                html += '<div class="d-flex gap-2"><div class="bv2-field flex-fill"><label>' +
                    "{{ __('Date') }}" + '</label><input type="date" class="form-control form-control-sm" ' +
                    'data-act="edit-date" value="' + esc(dmyToYmd(draft.date)) + '"></div>' +
                    '<div class="bv2-field flex-fill"><label>' + "{{ __('Start Time') }}" + '</label>' +
                    '<input type="time" class="form-control form-control-sm" data-act="edit-time" value="' +
                    esc(draft.startTime) + '"></div></div>';

                html += '<div class="bv2-field"><label>' + "{{ __('Status') }}" + '</label>' +
                    '<select class="form-control form-control-sm" data-act="edit-status">' +
                    statusOptionsHtml(draft.statusId) + '</select></div>';

                html += customFieldsHtml();

                html += '<div class="bv2-field"><label>' + "{{ __('Notes') }}" + '</label>' +
                    '<textarea class="form-control form-control-sm" rows="2" data-act="edit-notes">' +
                    esc(draft.notes) + '</textarea></div>';

                var staffName = '';

                (formData.staff || []).forEach(function (member) {
                    if (String(member.id) === String(draft.staffId)) {
                        staffName = member.title;
                    }
                });

                html += '<div class="bv2-card"><div class="bv2-card-header">' +
                    '<span>' + esc(staffName || "{{ __('Staff') }}") + '</span>' +
                    '</div><div class="bv2-card-body">';

                html += '<div class="bv2-field"><label>' + "{{ __('Staff') }}" + '</label>' +
                    '<select class="form-control form-control-sm" data-act="staff">' +
                    staffOptionsHtml(draft.staffId) + '</select></div>';

                // Start/end are derived, not typed: the shared start time drives
                // them and the service durations set the end.
                html += '<div class="d-flex gap-2"><div class="bv2-field flex-fill"><label>' +
                    "{{ __('Start') }}" + '</label><div class="bv2-readonly">' + esc(draft.startTime) +
                    '</div></div><div class="bv2-field flex-fill"><label>' + "{{ __('End') }}" +
                    '</label><div class="bv2-readonly">' + esc(endTime()) + '</div></div></div>';

                // Services are rows here rather than chips, same add model.
                html += '<div class="bv2-field"><label>' + "{{ __('Services') }}" + '</label>';

                if (!draft.services.length) {
                    html += '<div class="text-muted small mb-1">' + "{{ __('No services yet.') }}" + '</div>';
                }

                draft.services.forEach(function (service, serviceIndex) {
                    html += '<div class="bv2-service-row"><span>' + esc(service.name) + '</span>' +
                        '<span class="d-flex align-items-center gap-1">' +
                        overrideInputsHtml(service, serviceIndex) +
                        '<span class="bv2-chip-x" data-act="rm-service" data-index="' +
                        serviceIndex + '">&times;</span></span></div>';
                });

                html += '<div class="bv2-summary mt-1"><i class="ti ti-clock"></i> ' +
                    esc(durationLabel(totalDuration())) + ' &nbsp;|&nbsp; ' +
                    esc(money(totalPrice())) + '</div>' +
                    '<div class="d-flex gap-1"><select class="form-control form-control-sm" ' +
                    'data-act="service-pick">' + serviceOptionsHtml() + '</select>' +
                    '<button type="button" class="btn btn-sm btn-primary" data-act="add-service">' +
                    '<i class="ti ti-plus"></i></button></div></div>';

                html += '</div></div>';

                setBody(html);
                bindDraftEvents();

                setFooter([
                    {
                        label: "{{ __('Save Changes') }}",
                        className: 'btn btn-sm btn-primary flex-fill',
                        onClick: saveEdit
                    },
                    {
                        label: "{{ __('Back') }}",
                        className: 'btn btn-sm btn-light',
                        onClick: function () { openView(draft.id); }
                    }
                ]);
            }

            /* ===================== shared draft events ===================== */

            function findService(id) {
                var found = null;

                (formData.services || []).forEach(function (group) {
                    group.items.forEach(function (item) {
                        if (String(item.id) === String(id)) {
                            found = item;
                        }
                    });
                });

                return found;
            }

            function rerenderDraft() {
                if (panelMode === 'edit') {
                    renderEdit();
                } else {
                    renderCreate();
                }
            }

            function bindDraftEvents() {
                var body = document.getElementById('bv2-panel-body');

                body.querySelectorAll('[data-act]').forEach(function (el) {
                    var act = el.getAttribute('data-act');
                    if (act === 'rm-service') {
                        el.addEventListener('click', function () {
                            // Splice by position so repeated services survive.
                            var serviceIndex = parseInt(el.getAttribute('data-index'), 10);
                            draft.services.splice(serviceIndex, 1);
                            rerenderDraft();
                        });
                    } else if (act === 'add-service') {
                        el.addEventListener('click', function () {
                            var picker = body.querySelector('[data-act="service-pick"]');
                            var service = findService(picker ? picker.value : '');

                            if (!service) {
                                return;
                            }

                            // One click = one instance; the selection is kept so
                            // clicking + three times adds three.
                            draft.services.push({
                                id: service.id,
                                name: service.name,
                                duration: service.duration,
                                price: service.price
                            });
                            rerenderDraft();
                        });
                    } else if (act === 'staff') {
                        el.addEventListener('change', function () {
                            draft.staffId = el.value;
                            if (panelMode === 'edit') {
                                rerenderDraft();
                            }
                        });
                    } else if (act === 'date') {
                        el.addEventListener('change', function () {
                            draft.date = ymdToDmy(el.value);
                        });
                    } else if (act === 'time') {
                        el.addEventListener('change', function () {
                            draft.startTime = el.value;
                            rerenderDraft();
                        });
                    } else if (act === 'notes') {
                        // input, not change: no re-render, so focus is kept.
                        el.addEventListener('input', function () {
                            draft.notes = el.value;
                        });
                    } else if (act === 'status') {
                        el.addEventListener('change', function () {
                            draft.statusId = el.value;
                        });
                    } else if (act === 'svc-duration' || act === 'svc-price') {
                        var serviceIdx = parseInt(el.getAttribute('data-index'), 10);
                        var key = act === 'svc-duration' ? 'duration' : 'price';

                        // input updates state silently so typing keeps focus;
                        // change clamps and re-renders so the end time catches up.
                        el.addEventListener('input', function () {
                            draft.services[serviceIdx][key] = el.value === ''
                                ? 0
                                : Number(el.value);
                        });
                        el.addEventListener('change', function () {
                            var line = draft.services[serviceIdx];

                            // A cleared duration would post 0 and fail the
                            // server's min:1 rule, so floor it here instead.
                            if (key === 'duration') {
                                line.duration = Math.max(1, Math.round(Number(line.duration) || 1));
                            } else {
                                line.price = Math.max(0, Number(line.price) || 0);
                            }

                            rerenderDraft();
                        });
                    } else if (act === 'custom-field') {
                        el.addEventListener('input', function () {
                            draft.customFields[el.getAttribute('data-label')] = el.value;
                        });
                    } else if (act === 'location') {
                        el.addEventListener('change', function () {
                            draft.locationId = el.value;
                        });
                    } else if (act === 'clear-customer') {
                        el.addEventListener('click', function () {
                            draft.customer = null;
                            rerenderDraft();
                        });
                    } else if (act === 'new-customer') {
                        el.addEventListener('click', function () {
                            renderNewCustomerCard();
                        });
                    } else if (act === 'edit-date') {
                        el.addEventListener('change', function () {
                            draft.date = ymdToDmy(el.value);
                        });
                    } else if (act === 'edit-time') {
                        el.addEventListener('change', function () {
                            draft.startTime = el.value;
                            rerenderDraft();
                        });
                    } else if (act === 'edit-status') {
                        el.addEventListener('change', function () {
                            draft.statusId = el.value;
                        });
                    } else if (act === 'edit-notes') {
                        el.addEventListener('input', function () {
                            draft.notes = el.value;
                        });
                    }
                });

                bindCustomerTypeahead();
            }

            // Lightweight typeahead rather than Select2 — avoids the documented
            // dropdownParent/z-index problem with select boxes inside the panel.
            function bindCustomerTypeahead() {
                var input = document.getElementById('bv2-cust-input');
                var results = document.getElementById('bv2-ta-results');

                if (!input || !results) {
                    return;
                }

                var timer = null;

                input.addEventListener('input', function () {
                    clearTimeout(timer);
                    var term = input.value.trim();

                    if (term.length < 2) {
                        results.classList.remove('show');
                        return;
                    }

                    timer = setTimeout(function () {
                        fetch(CFG.customersUrl + '?q=' + encodeURIComponent(term), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(function (response) {
                                return response.json();
                            })
                            .then(function (payload) {
                                var rows = payload.results || [];

                                if (!rows.length) {
                                    results.innerHTML = '<div class="bv2-ta-item text-muted">' +
                                        "{{ __('No customers found.') }}" + '</div>';
                                    results.classList.add('show');
                                    return;
                                }

                                results.innerHTML = rows.map(function (row) {
                                    return '<div class="bv2-ta-item" data-id="' + esc(row.id) +
                                        '" data-name="' + esc(row.name) + '" data-mobile="' + esc(row.mobile) +
                                        '" data-email="' + esc(row.email) + '">' +
                                        '<div class="bv2-ta-name">' + esc(row.name) + '</div>' +
                                        '<div class="bv2-ta-meta">' +
                                        esc([row.mobile, row.email].filter(Boolean).join(' · ')) +
                                        '</div></div>';
                                }).join('');

                                results.classList.add('show');

                                results.querySelectorAll('.bv2-ta-item[data-id]').forEach(function (item) {
                                    item.addEventListener('click', function () {
                                        draft.customer = {
                                            id: item.getAttribute('data-id'),
                                            name: item.getAttribute('data-name'),
                                            mobile: item.getAttribute('data-mobile'),
                                            email: item.getAttribute('data-email')
                                        };
                                        results.classList.remove('show');
                                        renderCreate();
                                        loadCustomerHistory();
                                    });
                                });
                            })
                            .catch(function () {
                                results.classList.remove('show');
                            });
                    }, 250);
                });
            }

            // Previous/upcoming tables for the picked customer, shown below the
            // cards exactly as the detail view renders them.
            function loadCustomerHistory() {
                var container = document.getElementById('bv2-cust-history');

                if (!container || !draft.customer) {
                    return;
                }

                fetch(CFG.customerHistoryUrl.replace('__ID__', draft.customer.id), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        // The panel may have been re-rendered or closed meanwhile.
                        var target = document.getElementById('bv2-cust-history');

                        if (target) {
                            target.innerHTML =
                                historyTable("{{ __('Previous Appointments') }}", data.previous) +
                                historyTable("{{ __('Upcoming Appointments') }}", data.upcoming);
                        }
                    })
                    .catch(function () { /* history is optional context */ });
            }

            /* ===================== saves ===================== */

            // Server expects {service_id, duration, price}; the last two are the
            // per-booking overrides.
            function serviceLine(service) {
                return {
                    service_id: service.id,
                    duration: service.duration,
                    price: service.price
                };
            }

            function validateDraft() {
                if (panelMode !== 'edit' && !draft.customer) {
                    notify(T.needCustomer, 'error');
                    return false;
                }

                if (panelMode !== 'edit' && !draft.locationId) {
                    notify(T.needLocation, 'error');
                    return false;
                }

                if (!draft.staffId) {
                    notify(T.needStaff, 'error');
                    return false;
                }

                if (!draft.services.length) {
                    notify(T.needService, 'error');
                    return false;
                }

                if (!draft.startTime) {
                    notify(T.needTime, 'error');
                    return false;
                }

                return true;
            }

            function lockFooter(label) {
                var button = document.getElementById('bv2-panel-footer').querySelector('button');

                if (button) {
                    button.disabled = true;
                    button.textContent = label;
                }
            }

            function saveCreate() {
                if (!validateDraft()) {
                    return;
                }

                lockFooter(T.saving);

                var payload = {
                    location_id: draft.locationId,
                    customer_id: draft.customer.id,
                    custom_fields: draft.customFields,
                    staff_id: draft.staffId,
                    date: draft.date,
                    start_time: draft.startTime,
                    status_id: draft.statusId,
                    notes: draft.notes,
                    services: draft.services.map(serviceLine)
                };

                fetch(CFG.storeUrl, {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify(payload)
                })
                    .then(readJson)
                    .then(function (data) {
                        notify(data.message || T.saved, 'success');
                        closePanel();
                        calendar.refetchEvents();
                    })
                    .catch(function (error) {
                        notify(error.message || T.saveFailed, 'error');
                        renderCreate();
                    });
            }

            function saveEdit() {
                if (!validateDraft()) {
                    return;
                }

                lockFooter(T.saving);

                var payload = {
                    date: draft.date,
                    start_time: draft.startTime,
                    status_id: draft.statusId,
                    notes: draft.notes,
                    custom_fields: draft.customFields,
                    staff_id: draft.staffId,
                    services: draft.services.map(serviceLine)
                };

                // A real PUT, not a spoofed one: Laravel reads `_method` from the
                // form ParameterBag, which a JSON body never populates.
                fetch(CFG.updateUrl.replace('__ID__', draft.id), {
                    method: 'PUT',
                    headers: jsonHeaders(),
                    body: JSON.stringify(payload)
                })
                    .then(readJson)
                    .then(function (data) {
                        notify(data.message || T.saved, 'success');
                        calendar.refetchEvents();
                        openView(draft.id);
                    })
                    .catch(function (error) {
                        notify(error.message || T.saveFailed, 'error');
                        renderEdit();
                    });
            }

            // Surface the server's own validation message rather than a generic one.
            function readJson(response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.error || '');
                    }
                    return data;
                });
            }

            function changeStatus(id, statusId) {
                var body = new FormData();
                body.append('_token', csrf());
                body.append('appointment_id', id);
                body.append('status', statusId);

                fetch(CFG.statusUrl.replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('http ' + response.status);
                        }
                        calendar.refetchEvents();
                        openView(id);
                    })
                    .catch(function () {
                        notify(T.statusFailed, 'error');
                    });
            }

            function moveAppointment(info) {
                var event = info.event;
                var id = event.extendedProps.appointment_id;
                var resourceIds = event._def.resourceIds || [];
                var staffId = resourceIds.length ? resourceIds[0] : null;

                var date = localDmy(event.start);
                var time = localHm(event.start) + '-' + (event.end ? localHm(event.end) : localHm(event.start));

                if (!window.confirm(T.moveConfirm + ' ' + date + ' ' + time + '?')) {
                    info.revert();
                    return;
                }

                var body = new FormData();
                body.append('_token', csrf());
                body.append('date', date);
                body.append('time', time);
                if (staffId) {
                    body.append('staff_id', staffId);
                }

                fetch(CFG.moveUrl.replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('http ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function () {
                        calendar.refetchEvents();
                    })
                    .catch(function () {
                        info.revert();
                        notify(T.moveFailed, 'error');
                    });
            }

            /* ===================== selection guard ===================== */

            // Refuse a slot outside the staff member's working windows.
            function withinBusinessHours(resourceId, start) {
                var windows = resourceHours[String(resourceId)];

                if (windows === false) {
                    return false;
                }
                if (!windows || !windows.length) {
                    return true; // nothing to check against
                }

                var minutes = start.getHours() * 60 + start.getMinutes();

                for (var i = 0; i < windows.length; i++) {
                    if (minutes >= toMinutes(windows[i].startTime) && minutes < toMinutes(windows[i].endTime)) {
                        return true;
                    }
                }

                return false;
            }

            function handleSelect(info) {
                var resourceId = info.resource ? info.resource.id : null;

                if (resourceId && !withinBusinessHours(resourceId, info.start)) {
                    notify(T.outsideHours, 'error');
                    calendar.unselect();
                    return;
                }

                openCreate({
                    staffId: resourceId,
                    date: localDmy(info.start),
                    time: localHm(info.start)
                });

                calendar.unselect();
            }

            /* ===================== sizing / toolbar ===================== */

            function computeHeight() {
                var el = document.getElementById('bv2-calendar');

                if (!el) {
                    return 420;
                }

                return Math.max(420, window.innerHeight - el.getBoundingClientRect().top - 40);
            }

            function fitHeight() {
                if (calendar) {
                    calendar.setOption('height', computeHeight());
                }
            }

            function syncToolbar() {
                var date = calendar.getDate();

                document.getElementById('bv2-date').value = localYmd(date);
                document.getElementById('bv2-title').textContent = date.toLocaleDateString(CFG.locale, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function reload() {
                // Staff and services are location-scoped, so drop the cache.
                formData = null;
                calendar.refetchResources();
                calendar.refetchEvents();
            }

            function bindToolbar() {
                document.getElementById('bv2-prev').addEventListener('click', function () {
                    calendar.prev();
                });

                document.getElementById('bv2-next').addEventListener('click', function () {
                    calendar.next();
                });

                document.getElementById('bv2-today').addEventListener('click', function () {
                    calendar.today();
                });

                document.getElementById('bv2-date').addEventListener('change', function () {
                    if (this.value) {
                        // Split rather than new Date(value): the string form is
                        // parsed as UTC and lands on the previous day west of UTC.
                        var parts = this.value.split('-');
                        calendar.gotoDate(new Date(
                            parseInt(parts[0], 10),
                            parseInt(parts[1], 10) - 1,
                            parseInt(parts[2], 10)
                        ));
                    }
                });

                document.getElementById('bv2-location').addEventListener('change', reload);
                document.getElementById('bv2-staff-mode').addEventListener('change', reload);
                document.getElementById('bv2-refresh').addEventListener('click', reload);
                document.getElementById('bv2-panel-close').addEventListener('click', closePanel);

                // The header "+" — unlike a calendar-slot click, it has no
                // date/time/staff to prefill from, so it opens the same create
                // panel with today's date (whatever the calendar is currently
                // viewing), the next slot-aligned time, and staff left for the
                // user to pick — Staff/Date/Time are editable fields on that
                // panel either way.
                var newApptButton = document.getElementById('bv2-toolbar-new-appt');
                if (newApptButton) {
                    newApptButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        openCreate({
                            staffId: '',
                            date: localDmy(calendar.getDate()),
                            time: nowRoundedToSlot()
                        });
                    });
                }
            }

            /* ===================== boot ===================== */

            function boot() {
                var el = document.getElementById('bv2-calendar');

                if (!el || typeof FullCalendar === 'undefined') {
                    return;
                }

                calendar = window.bookingV2Calendar = new FullCalendar.Calendar(el, {
                    schedulerLicenseKey: CFG.license,
                    initialView: 'resourceTimeGridDay',
                    headerToolbar: false,
                    locale: CFG.locale,
                    height: computeHeight(),
                    firstDay: CFG.weekStart,
                    slotDuration: hhmmss(CFG.slotInterval),
                    slotLabelInterval: hhmmss(CFG.slotInterval < 15 ? CFG.slotInterval * 2 : CFG.slotInterval),
                    snapDuration: hhmmss(CFG.slotInterval),
                    slotMinTime: '07:00:00',
                    slotMaxTime: '23:00:00',
                    scrollTime: '07:00:00',
                    scrollTimeReset: false,
                    nowIndicator: true,
                    allDaySlot: false,
                    editable: CFG.canEdit,
                    eventDurationEditable: false,
                    selectable: CFG.canCreate,
                    selectMirror: true,
                    refetchResourcesOnNavigate: true,
                    resourceOrder: 'title',
                    resourceAreaColumns: [{ headerContent: "{{ __('Staff') }}", field: 'title' }],
                    resources: fetchResources,
                    events: fetchEvents,

                    eventContent: function (info) {
                        var props = info.event.extendedProps;

                        if ((props.type || '') === 'block_time') {
                            return {
                                html: '<div class="bv2-block-inner"><i class="ti ti-lock-filled"></i>' +
                                    '<div class="bv2-block-text"><span>' + esc(info.event.title) + '</span>' +
                                    (props.reason
                                        ? '<span class="bv2-block-reason">' + esc(props.reason) + '</span>'
                                        : '') +
                                    '</div></div>'
                            };
                        }

                        return {
                            html: '<div class="fc-event-main-frame">' +
                                (info.timeText
                                    ? '<span class="fc-event-time">' + esc(info.timeText) + '</span>'
                                    : '') +
                                (info.event.title
                                    ? '<span class="fc-event-title">' + esc(info.event.title) + '</span>'
                                    : '') +
                                '</div>'
                        };
                    },

                    eventDidMount: function (info) {
                        var props = info.event.extendedProps;

                        if ((props.type || '') === 'block_time') {
                            info.el.classList.add('bv2-block-event');
                            info.el.setAttribute('title', [props.reason, props.description]
                                .filter(Boolean).join(' · ') || info.event.title);
                            return;
                        }

                        info.el.setAttribute('title', [props.customer_name, props.service_name]
                            .filter(Boolean).join(' · ') || info.event.title);
                    },

                    eventClick: function (info) {
                        info.jsEvent.preventDefault(); // the url is data, not a destination

                        if ((info.event.extendedProps.type || '') === 'block_time') {
                            return;
                        }

                        openView(info.event.extendedProps.appointment_id);
                    },

                    eventDrop: moveAppointment,

                    // No dateClick handler: with selectable on, a plain click is
                    // already a one-slot selection, so select covers both and
                    // wiring dateClick too would open the panel twice.
                    select: handleSelect,

                    datesSet: function () {
                        syncToolbar();
                    },

                    viewDidMount: function () {
                        // The cells do not exist until after the view paints.
                        setTimeout(function () {
                            document.querySelectorAll('#bv2-calendar .fc-non-business')
                                .forEach(function (cell) {
                                    cell.setAttribute('title', T.outside);
                                });
                        }, 150);
                    }
                });

                calendar.render();
                bindToolbar();
                syncToolbar();
                openProposalPrefillFromUrl();

                window.addEventListener('resize', fitHeight);
            }

            // "Convert to Appointment" on an accepted quotation (proposal/modern-view.blade.php)
            // lands here as ?convert_proposal=<encrypted id> rather than posting
            // any booking data itself — this is the only place that knows how
            // to turn a customer + a list of services into a real draft
            // appointment, so it fetches the quotation's summary and opens the
            // same New Appointment panel everyone else uses, pre-filled.
            function openProposalPrefillFromUrl() {
                var encId = new URLSearchParams(window.location.search).get('convert_proposal');

                if (!encId || !CFG.canCreate) {
                    return;
                }

                // One-shot: a refresh of this page should show a normal
                // calendar, not silently reopen the same prefill.
                var url = new URL(window.location.href);
                url.searchParams.delete('convert_proposal');
                window.history.replaceState({}, '', url);

                fetch(CFG.proposalPrefillUrl.replace('__ID__', encId), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data.error) {
                            notify(data.error, 'error');
                            return;
                        }

                        openCreate({
                            staffId: '',
                            date: localDmy(calendar.getDate()),
                            time: nowRoundedToSlot(),
                            customer: data.customer,
                            serviceIds: data.serviceIds
                        });
                    })
                    .catch(function () {
                        notify(T.loadFailed, 'error');
                    });
            }

            // Measure after first paint so the height fit sees a laid-out page.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    requestAnimationFrame(boot);
                });
            } else {
                requestAnimationFrame(boot);
            }
        })();
    </script>
@endpush
