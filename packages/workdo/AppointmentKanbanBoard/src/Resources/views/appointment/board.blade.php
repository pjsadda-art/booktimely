@extends('layouts.main')

@section('page-title')
{{ __('Appointment Kanban Board') }}
@endsection

@section('page-breadcrumb')
{{ __('Appointment Kanban Board') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/workdo/AppointmentKanbanBoard/src/Resources/assets/css/board.css') }}">
@endpush

@section('content')
@if (!empty($statuses) && Laratrust::hasPermission('appointment kanban board manage'))
    <div class="row">
        <div class="col-sm-12">
            <div class="row kanban-wrapper horizontal-scroll-cards pt-3" data-toggle="dragula"
                data-containers='{{ json_encode($statusClass) }}'>
                @foreach ($statuses as $status)
                    @if ($status->show_in_kanban)
                    @php $stColor = '#' . (!empty($status->status_color) ? ltrim($status->status_color, '#') : '6c757d'); @endphp
                    <div class="col" id="backlog">
                        <div class="card card-list" style="border-top: 4px solid {{ $stColor }};">
                            <div class="card-header d-flex align-items-center justify-content-between p-3">
                                <h4 class="mb-0">{{ $status->title }}</h4>
                                    <button class="btn-submit btn btn-md btn-primary btn-icon px-1 py-0">
                                        <span
                                            class="badge badge-secondary rounded-pill count">{{ $status->tasks->count() }}</span>
                                    </button>
                            </div>
                            <div id="{{ 'task-list-' . str_replace(' ', '_', $status->id) }}" data-status="{{ $status->id }}"
                                class="card-body kanban-box p-3">
                                @foreach ($status->tasks as $task)
                                    <div class="card" id="{{ $task->id }}">
                                        <div class="card-header position-relative border-0 p-3">
                                            <div>
                                                <div class="badge p-2 px-3 me-2" style="background-color: {{ $stColor }};">
                                                    <a href="javascript:void(0)" class="" data-ajax-popup="true" data-size="lg"
                                                        title="{{ __('View') }}" data-title="{{ __('Appointment Details') }}"
                                                        data-bs-toggle="tooltip"
                                                        data-url="{{ route('appointment.show', $task->id) }}"><span
                                                            class="text-white">
                                                            {{ App\Models\Appointment::appointmentNumberWithFormat($task->id, $company_settings) }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <i class="feather icon-more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" data-ajax-popup="true" data-size="md"
                                                            data-title="{{ __('Change Status') }}"
                                                            data-url="{{ route('appointment.status.change', $task->id) }}"><i
                                                                class="ti ti-pencil"></i> {{ __('Change Status') }}</a>
                                                        <a class="dropdown-item" data-ajax-popup="true" data-size="lg"
                                                            data-title="{{ __('Appointment Detail') }}"
                                                            data-url="{{ route('appointment.show', $task->id) }}"><i
                                                                class="ti ti-eye"></i> {{ __('view') }}
                                                        </a>
                                                        @permission('appointment edit')
                                                        <a class="dropdown-item" data-ajax-popup="true" data-size="md"
                                                            data-title="{{ __('Edit Task') }}"
                                                            data-url="{{ route('appointment.edit', $task->id) }}"><i
                                                                class="ti ti-pencil"></i> {{ __('Edit') }}</a>
                                                        @endpermission
                                                        @permission('appointment delete')
                                                        <form id="delete-form-{{ $task->id }}"
                                                            action="{{ route('appointment.destroy', $task->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <a href="#" class="dropdown-item bs-pass-para show_confirm"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $task->id }}"> <i
                                                                    class="ti ti-trash"></i>
                                                                {{ __('Delete') }}
                                                            </a>
                                                        </form>
                                                        @endpermission
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0 p-3">
                                            <div class="card-body-inner">
                                                <div class="card-content-top">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-column gap-1">
                                                        <h5 class="m-0 d-flex gap-2">
                                                        <span><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M10 0C7.09223 0 4.72656 2.36566 4.72656 5.27344C4.72656 8.18121 7.09223 10.5469 10 10.5469C12.9078 10.5469 15.2734 8.18121 15.2734 5.27344C15.2734 2.36566 12.9078 0 10 0ZM10 9.375C7.7384 9.375 5.89844 7.53504 5.89844 5.27344C5.89844 3.01184 7.7384 1.17188 10 1.17188C12.2616 1.17188 14.1016 3.01184 14.1016 5.27344C14.1016 7.53504 12.2616 9.375 10 9.375Z"
                                                                        fill="#060606" />
                                                                    <path
                                                                        d="M16.5612 13.992C15.1174 12.5261 13.2035 11.7188 11.1719 11.7188H8.82812C6.79656 11.7188 4.88258 12.5261 3.43883 13.992C2.00215 15.4507 1.21094 17.3763 1.21094 19.4141C1.21094 19.7377 1.47328 20 1.79688 20H18.2031C18.5267 20 18.7891 19.7377 18.7891 19.4141C18.7891 17.3763 17.9979 15.4507 16.5612 13.992ZM2.40859 18.8281C2.70215 15.5045 5.46918 12.8906 8.82812 12.8906H11.1719C14.5308 12.8906 17.2979 15.5045 17.5914 18.8281H2.40859Z"
                                                                        fill="#060606" />
                                                                </svg>
                                                            </span>
                                                            {{ !empty($task->CustomerData) ? $task->CustomerData->name : ($task->name ?? 'Guest') . ' (Guest)' }}
                                                        </h5>

                                                        <a class="task-title d-flex gap-2 text-break">

                                                            <span><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <g clip-path="url(#clip0_103_209)">
                                                                        <path
                                                                            d="M20 4.35101C20 3.33617 19.1743 2.5105 18.1595 2.5105H1.84052C0.825863 2.51054 0.000351564 3.33581 0 4.35133V4.35164V15.6485C0 16.6761 0.834066 17.4897 1.84114 17.4897H18.1588C19.1863 17.4897 20 16.6556 20 15.6485V4.35164C20 4.35156 20 4.35148 20 4.3514C20 4.35129 20 4.35117 20 4.35101ZM1.84052 3.68242H18.1595C18.5282 3.68242 18.8281 3.98238 18.8281 4.35199C18.8281 4.5484 18.7305 4.7307 18.5668 4.83976L10.3713 10.3037C10.1458 10.454 9.8543 10.4541 9.62875 10.3037C9.62875 10.3037 1.43301 4.83965 1.43317 4.83976C1.43321 4.8398 1.43305 4.83969 1.43301 4.83965C1.2695 4.7307 1.17188 4.5484 1.17188 4.35101C1.17188 3.98234 1.47184 3.68242 1.84052 3.68242ZM18.1589 16.3178H1.84114C1.47633 16.3178 1.17188 16.0228 1.17188 15.6485V6.07403L8.97867 11.2787C9.2889 11.4855 9.64445 11.5889 10 11.5889C10.3556 11.5889 10.7112 11.4855 11.0214 11.2787L18.8281 6.07403V15.6486C18.8281 16.0133 18.5332 16.3178 18.1589 16.3178Z"
                                                                            fill="#060606" />
                                                                    </g>
                                                                    <defs>
                                                                        <clipPath id="clip0_103_209">
                                                                            <rect width="20" height="20" fill="white" />
                                                                        </clipPath>
                                                                    </defs>
                                                                </svg></span>
                                                            {{ !empty($task->CustomerData) ? $task->CustomerData->customer->email : $task->email }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-content-bottom">
                                                    <div class="card-content d-flex align-items-center justify-content-between">
                                                        <div class="action-item d-flex align-items-center gap-2 w-50 justify-content-center">
                                                            <span class="d-flex">
                                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <g clip-path="url(#clip0_103_207)">
                                                                        <path
                                                                            d="M16.7071 1.35049H15.5681V0.594059C15.5681 0.436505 15.5055 0.285404 15.3941 0.173996C15.2826 0.0625884 15.1315 0 14.9739 0C14.8163 0 14.6651 0.0625884 14.5537 0.173996C14.4422 0.285404 14.3796 0.436505 14.3796 0.594059V1.35049H5.62036V0.594059C5.62036 0.436505 5.55775 0.285404 5.44631 0.173996C5.33487 0.0625884 5.18372 0 5.02611 0C4.8685 0 4.71735 0.0625884 4.60591 0.173996C4.49447 0.285404 4.43186 0.436505 4.43186 0.594059V1.35049H3.29188C2.55365 1.35154 1.84597 1.64524 1.32406 2.16717C0.80214 2.68911 0.508599 3.39666 0.507812 4.13465V17.2168C0.508861 17.9547 0.802518 18.662 1.32441 19.1837C1.84629 19.7054 2.55382 19.999 3.29188 20H16.7071C17.4456 19.9995 18.1537 19.706 18.6759 19.184C19.1981 18.6619 19.4917 17.9541 19.4922 17.2158V4.13465C19.4917 3.39641 19.1981 2.68855 18.6759 2.16653C18.1537 1.64452 17.4456 1.35102 16.7071 1.35049ZM18.3037 17.2158C18.3032 17.639 18.1348 18.0446 17.8355 18.3438C17.5362 18.643 17.1304 18.8114 16.7071 18.8119H3.29188C2.86887 18.8114 2.46334 18.6431 2.16423 18.3441C1.86511 18.0451 1.69684 17.6397 1.69632 17.2168V7.09703H18.3047L18.3037 17.2158ZM18.3037 5.90891H1.69632V4.13465C1.69658 3.71161 1.86474 3.30595 2.16388 3.00672C2.46302 2.70749 2.8687 2.53914 3.29188 2.53861H4.43186V3.22178C4.43186 3.37934 4.49447 3.53044 4.60591 3.64185C4.71735 3.75325 4.8685 3.81584 5.02611 3.81584C5.18372 3.81584 5.33487 3.75325 5.44631 3.64185C5.55775 3.53044 5.62036 3.37934 5.62036 3.22178V2.53861H14.3806V3.22178C14.3806 3.37934 14.4432 3.53044 14.5547 3.64185C14.6661 3.75325 14.8173 3.81584 14.9749 3.81584C15.1325 3.81584 15.2836 3.75325 15.3951 3.64185C15.5065 3.53044 15.5691 3.37934 15.5691 3.22178V2.53861H16.7081C17.1312 2.53914 17.5369 2.70732 17.8361 3.00631C18.1354 3.3053 18.3039 3.7107 18.3047 4.13366L18.3037 5.90891ZM3.87425 10.7139C3.87425 10.4691 3.94686 10.2298 4.0829 10.0263C4.21893 9.82275 4.41229 9.66412 4.6385 9.57045C4.86472 9.47677 5.11365 9.45226 5.3538 9.50002C5.59396 9.54777 5.81455 9.66564 5.98769 9.83873C6.16083 10.0118 6.27874 10.2323 6.32651 10.4724C6.37428 10.7125 6.34977 10.9613 6.25606 11.1875C6.16236 11.4136 6.00368 11.6069 5.80009 11.7429C5.59649 11.8789 5.35713 11.9515 5.11228 11.9515C4.78393 11.9515 4.46904 11.8211 4.23686 11.589C4.00469 11.3569 3.87425 11.0421 3.87425 10.7139ZM8.76197 10.7139C8.76197 10.4691 8.83458 10.2298 8.97062 10.0263C9.10666 9.82275 9.30001 9.66412 9.52623 9.57045C9.75245 9.47677 10.0014 9.45226 10.2415 9.50002C10.4817 9.54777 10.7023 9.66564 10.8754 9.83873C11.0486 10.0118 11.1665 10.2323 11.2142 10.4724C11.262 10.7125 11.2375 10.9613 11.1438 11.1875C11.0501 11.4136 10.8914 11.6069 10.6878 11.7429C10.4842 11.8789 10.2449 11.9515 10 11.9515C9.67166 11.9515 9.35676 11.8211 9.12459 11.589C8.89241 11.3569 8.76197 11.0421 8.76197 10.7139ZM13.6497 10.7139C13.6497 10.4691 13.7223 10.2298 13.8583 10.0263C13.9944 9.82275 14.1877 9.66412 14.414 9.57045C14.6402 9.47677 14.8891 9.45226 15.1293 9.50002C15.3694 9.54777 15.59 9.66564 15.7631 9.83873C15.9363 10.0118 16.0542 10.2323 16.102 10.4724C16.1497 10.7125 16.1252 10.9613 16.0315 11.1875C15.9378 11.4136 15.7791 11.6069 15.5755 11.7429C15.3719 11.8789 15.1326 11.9515 14.8877 11.9515C14.5594 11.9515 14.2445 11.8211 14.0123 11.589C13.7801 11.3569 13.6497 11.0421 13.6497 10.7139ZM3.87425 14.7525C3.87425 14.5077 3.94686 14.2684 4.0829 14.0649C4.21893 13.8614 4.41229 13.7027 4.6385 13.6091C4.86472 13.5154 5.11365 13.4909 5.3538 13.5386C5.59396 13.5864 5.81455 13.7043 5.98769 13.8773C6.16083 14.0504 6.27874 14.271 6.32651 14.511C6.37428 14.7511 6.34977 14.9999 6.25606 15.2261C6.16236 15.4522 6.00368 15.6455 5.80009 15.7815C5.59649 15.9175 5.35713 15.9901 5.11228 15.9901C4.78393 15.9901 4.46904 15.8597 4.23686 15.6276C4.00469 15.3955 3.87425 15.0807 3.87425 14.7525ZM8.76197 14.7525C8.76158 14.5076 8.83386 14.2681 8.96966 14.0644C9.10545 13.8606 9.29867 13.7016 9.52485 13.6076C9.75104 13.5137 10 13.4889 10.2403 13.5364C10.4806 13.5839 10.7014 13.7017 10.8747 13.8747C11.0481 14.0477 11.1662 14.2682 11.2141 14.5083C11.262 14.7484 11.2376 14.9974 11.144 15.2237C11.0503 15.4499 10.8917 15.6433 10.688 15.7794C10.4844 15.9155 10.2449 15.9881 10 15.9881C9.672 15.9881 9.3574 15.858 9.12528 15.6263C8.89317 15.3947 8.7625 15.0804 8.76197 14.7525ZM13.6497 14.7525C13.6497 14.5077 13.7223 14.2684 13.8583 14.0649C13.9944 13.8614 14.1877 13.7027 14.414 13.6091C14.6402 13.5154 14.8891 13.4909 15.1293 13.5386C15.3694 13.5864 15.59 13.7043 15.7631 13.8773C15.9363 14.0504 16.0542 14.271 16.102 14.511C16.1497 14.7511 16.1252 14.9999 16.0315 15.2261C15.9378 15.4522 15.7791 15.6455 15.5755 15.7815C15.3719 15.9175 15.1326 15.9901 14.8877 15.9901C14.5594 15.9901 14.2445 15.8597 14.0123 15.6276C13.7801 15.3955 13.6497 15.0807 13.6497 14.7525Z"
                                                                            fill="#060606" />
                                                                    </g>
                                                                    <defs>
                                                                        <clipPath id="clip0_103_207">
                                                                            <rect width="20" height="20" fill="white" />
                                                                        </clipPath>
                                                                    </defs>
                                                                </svg>
                                                            </span>
                                                            {{ company_date_formate($task->date) }}
                                                        </div>
                                                        <div
                                                            class="action-item text-end d-flex align-items-center gap-2 w-50 justify-content-center">
                                                            <span class="d-flex">
                                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <g clip-path="url(#clip0_103_206)">
                                                                        <path
                                                                            d="M10 20C15.5133 20 20 15.5133 20 10C20 4.48668 15.5134 0 10 0C4.48662 0 0 4.48668 0 10C0 15.5133 4.48668 20 10 20ZM10 1.33331C14.78 1.33331 18.6667 5.21996 18.6667 10C18.6667 14.78 14.78 18.6667 10 18.6667C5.21996 18.6667 1.33331 14.78 1.33331 10C1.33331 5.21996 5.22002 1.33331 10 1.33331Z"
                                                                            fill="#060606" />
                                                                        <path
                                                                            d="M12.9168 13.1867C13.0402 13.2866 13.1868 13.3333 13.3335 13.3333C13.5302 13.3333 13.7235 13.2467 13.8535 13.0833C14.0835 12.7967 14.0368 12.3767 13.7501 12.1467L10.6668 9.67999V4.66666C10.6668 4.29998 10.3668 4 10.0002 4C9.63348 4 9.3335 4.29998 9.3335 4.66666V10C9.3335 10.2034 9.42685 10.3933 9.58349 10.52L12.9168 13.1867Z"
                                                                            fill="#060606" />
                                                                    </g>
                                                                    <defs>
                                                                        <clipPath id="clip0_103_206">
                                                                            <rect width="20" height="20" fill="white" />
                                                                        </clipPath>
                                                                    </defs>
                                                                </svg>
                                                            </span>
                                                            {{ $task->time }}
                                                        </div>
                                                    </div>

                                                    <div class="info-wrp d-flex flex-wrap">
                                                        <div
                                                            class="card-content d-flex gap-1 align-items-center justify-content-between flex-column mb-0">
                                                            <h5 class="m-0">
                                                                {{ __('Staff')}}
                                                            </h5>

                                                            <a class="task-title text-center">
                                                                {{ !empty(optional($task->StaffData)) ? optional($task->StaffData)->name : "-" }}
                                                            </a>
                                                        </div>
                                                        <div
                                                            class="card-content d-flex gap-1 align-items-center justify-content-between flex-column mb-0">
                                                            <h5 class="m-0 border-start">
                                                                {{ __('Service')}}
                                                            </h5>

                                                            <a class="task-title text-center">
                                                                {{ !empty(optional($task->ServiceData)) ? optional($task->ServiceData)->name : "-" }}
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="card-content d-flex gap-3 align-items-center justify-content-between">
                                                        <h5 class="m-0">
                                                            {{ __('Payment')}}
                                                        </h5>
                                                        <div class="action-item text-end">
                                                            <span
                                                                class="bg-primary p-1 px-2 payment-btn d-block">{{ $task->payment_type }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <span class="empty-container" data-placeholder="Empty"></span>
                            </div>
                        </div>
                    </div>
                     @endif
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="container mt-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="page-error">
                    <div class="page-inner">
                        <h1>404</h1>
                        <div class="page-description">
                            {{ __('Page Not Found') }}
                        </div>
                        <div class="page-search">
                            <p class="text-muted mt-3">
                                {{ __("It's looking like you may have taken a wrong turn. Don't worry... it happens to the best of us. Here's a little tip that might help you get back on track.") }}
                            </p>
                            <div class="mt-3">
                                <a class="btn-return-home badge-blue" href="{{ route('home') }}"><i
                                        class="fas fa-reply"></i> {{ __('Return Home') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
@if (!empty($statuses) && Laratrust::hasPermission('appointment kanban board manage'))
    @push('scripts')
        <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script src="{{ asset('js/letter.avatar.js') }}"></script>
        <script>
            ! function (a) {
                "use strict";
                var t = function () {
                    this.$body = a("body")
                };
                t.prototype.init = function () {
                    a('[data-toggle="dragula"]').each(function () {
                        var t = a(this).data("containers"),
                            n = [];
                        if (t)
                            for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                        else n = [a(this)[0]];
                        var r = a(this).data("handleclass");
                        r ? dragula(n, {
                            moves: function (a, t, n) {
                                return n.classList.contains(r)
                            }
                        }) : dragula(n).on('drop', function (el, target, source, sibling) {
                            var sort = [];
                            $("#" + target.id + " > div").each(function (key) {
                                sort[key] = $(this).attr('id');
                            });
                            var id = el.id;
                            var old_status = $("#" + source.id).data('status');
                            var new_status = $("#" + target.id).data('status');

                            $("#" + source.id).parents('.card-list').find('.count').text($("#" + source.id +
                                " > div").length);
                            $("#" + target.id).parents('.card-list').find('.count').text($("#" + target.id +
                                " > div").length);
                            $.ajax({
                                url: '{{ route('appointment.updateOrder') }}',
                                type: 'POST',
                                data: {
                                    id: id,
                                    sort: sort,
                                    new_status: new_status,
                                    old_status: old_status,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function (data) {
                                    toastrs('Success', 'Appointment status updated successfully.', 'success');
                                    // toastrs('Error', 'This operation is not perform due to demo mode.', 'error');
                                },
                                error: function (err) {
                                    toastrs('Error', 'Something went wrong!', 'error');
                                }
                            });
                        });
                    })
                }, a.Dragula = new t, a.Dragula.Constructor = t
            }(window.jQuery),
                function (a) {
                    "use strict";
                    a.Dragula.init();
                }(window.jQuery);
        </script>
    @endpush
@endif
