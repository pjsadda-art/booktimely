@extends('layouts.main')
@section('page-title')
    {{ __('Add-on Manager') }}
@endsection
@section('page-breadcrumb')
    {{ __('Add-on Manager') }}
@endsection
@push('css')
    <style>
        .system-version h5 {
            position: absolute;
            bottom: -44px;
            right: 27px;
        }

        .center-text {
            display: flex;
            flex-direction: column;
        }

        .center-text .text-primary {
            font-size: 14px;
            margin-top: 5px;
        }

        .theme-main {
            display: flex;
            align-items: center;
        }

        .theme-main .theme-avtar {
            margin-right: 15px;
        }

        @media only screen and (max-width: 575px) {
            .system-version h5 {
                position: unset;
                margin-bottom: 0px;
            }

            .system-version {
                text-align: center;
                margin-bottom: -22px;
            }
        }
    </style>
@endpush

@php
    $categories = array_map(function ($item) {
        return [
            'name' => $item->name,
            'icon' => $item->icon,
        ];
    }, $addons);

    // $totalAddOns = array_sum(
    //     array_map(function ($element) {
    //         return count($element->add_ons);
    //     }, $addons),
    // );
@endphp
@section('page-action')
    <div>
        <a href="{{ route('module.add') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title=""
            data-bs-original-title="{{ __('Module Setup') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row justify-content-center px-0">
        <!-- [ sample-page ] start -->
        <div class=" col-12">
            <div class="add-on-banner mb-4">
                <img src="{{ asset('images/add-on-banner-layer.png') }}" class="banner-layer" alt="banner-layer">
                <div class="row  row-gap align-items-center">
                    <div class="col-xxl-4 col-md-6 col-12">
                        <div class="add-on-banner-image">
                            <img src="{{ asset('images/add-on-banner-image.png') }}" alt="banner-image" >
                        </div>
                    </div>
                    <div class="col-xxl-4 col-md-6 col-12">
                        <div class="add-on-banner-content text-center ">
                            <a href="https://workdo.io/product-category/bookinggo-saas-add-ons/bookinggo-theme/?utm_source=main&utm_medium=bookinggo&utm_campaign=btn" class="btn btn-light mb-md-3 mb-2">
                                <img src="https://workdo.io/wp-content/uploads/2023/03/favicon.jpg" alt="">
                                <span>{{ __('Click Here') }}</span>
                            </a>
                            <h2>{{ __('Buy More Add-on') }}</h2>
                            <p>+{{ count($modules) }}<span>{{ __('Premium Add-on') }}</span></p>
                        </div>
                    </div>
                    <div class="col-xxl-4 col-12">
                        <div class="add-on-btn d-flex flex-wrap align-items-center justify-content-xxl-end justify-content-center gap-2">
                              <a class="btn btn-primary" href="https://workdo.io/product-category/bookinggo-saas-add-ons/bookinggo-theme/?utm_source=main&utm_medium=bookinggo&utm_campaign=btn" target="new">
                                {{ __('Buy More Add-on') }}
                              </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="event-cards row px-0 mb-4">
            @if (count($devModules) > 0)
                <h3>{{ __('Below Packages Is Not Register') }}</h3>
                @foreach ($devModules as $devModule)
                    @php
                        $id = strtolower(preg_replace('/\s+/', '_', $devModule['name']));
                    @endphp
                    @if (!isset($devModule->display) || $devModule->display == true)
                        <div class="col-xxl-2 col-xl-3 col-md-4 col-sm-6 product-card ">
                            <div class="card disable_module">
                                <div class="product-img">
                                    <div class="theme-main">
                                        <div class="theme-avtar">
                                            <img src="{{ $devModule['image'] }}" alt="{{ $devModule['name'] }}"
                                                class="img-user width-100">
                                        </div>
                                        <div class="center-text">
                                            <small class="text-muted">
                                                <span class="badge bg-danger">{{ __('Disable') }}</span>
                                            </small>
                                            <small
                                                class="text-primary">{{ __('V') }}{{ sprintf('%.1f', $devModule['version']) }}</small>
                                        </div>
                                    </div>
                                    <div class="checkbox-custom">
                                        <div class="btn-group card-option">
                                        </div>
                                    </div>
                                </div>
                                <div class="product-content">
                                    <h4 class="text-capitalize"> {{ $devModule['alias'] }}</h4>
                                    <p class="text-muted text-sm mb-0">
                                        {{ $devModule['description'] ?? '' }}
                                    </p>

                                    <a href="{{ route('software.details', $devModule['alias']) }}" target="_new"
                                        class="btn  btn-outline-secondary w-100 mt-2">{{ __('How To Register') }}</a>

                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
            <h2 class="mb-4">{{ __('Installed Add-on') }}</h2>
            @foreach ($modules as $module)
                @php
                    $id = strtolower(preg_replace('/\s+/', '_', $module->name));
                @endphp
                @if (!isset($module->display) || $module->display == true)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 product-card ">
                        <div class="card {{ $module->isEnabled() ? 'enable_module' : 'disable_module' }}">
                            <div class="product-img">
                                <div class="theme-main">
                                    <div class="theme-avtar">
                                        <img src="{{ $module->image }}" alt="{{ $module->name }}" class="img-user"
                                            style="max-width: 100%">
                                    </div>
                                    <div class="center-text">
                                        <small class="text-muted">
                                            @if ($module->isEnabled())
                                                <span class="badge bg-success">{{ __('Enable') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Disable') }}</span>
                                            @endif
                                        </small>
                                        <small
                                            class="text-primary">{{ __('V') }}{{ sprintf('%.1f', $module->version) }}</small>
                                    </div>
                                </div>
                                <div class="checkbox-custom">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            @if ($module->isEnabled())
                                                <a href="#!" class="dropdown-item module_change"
                                                    data-id="{{ $id }}">
                                                    <span>{{ __('Disable') }}</span>
                                                </a>
                                            @else
                                                <a href="#!" class="dropdown-item module_change"
                                                    data-id="{{ $id }}">
                                                    <span>{{ __('Enable') }}</span>
                                                </a>
                                            @endif
                                            <form action="{{ route('module.enable') }}" method="POST"
                                                id="form_{{ $id }}">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $module->name }}">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product-content">
                                <h4 class="text-capitalize"> {{ $module->alias }}</h4>
                                <p class="text-muted text-sm mb-0">
                                    {{ $module->description ?? '' }}
                                </p>
                                <a href="{{ route('software.details', $module->alias) }}" target="_new"
                                    class="btn  btn-outline-secondary w-100 mt-2">{{ __('View Details') }}</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>


        <h3 class="mb-3">{{ __('Explore Add-on') }}</h3>
        <div class="col-xl-12">
            <div class="addon-nav mb-4">
                <ul class="nav nav-pills gap-2">
                    @foreach ($categories as $key => $category)
                        <li class="nav-item" role="presentation">
                            <a href="#tab-{{ $key }}" class="nav-link {{ $key == '0' ? 'active' : '' }}"
                                data-bs-toggle="pill">
                                <i class="me-1 {{ $category['icon'] }}"></i> {{ $category['name'] }} <div
                                    class="float-end">
                                </div></a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-xl-12">
                <div class="tab-content">
                    @foreach ($addons as $key => $addon)
                        <div id="tab-{{ $key }}" class="tab-pane fade {{ $key == '0' ? 'active show' : '' }} {{ $key == '1' ? 'addon-theme-card' : '' }}"
                            role="tabpanel">
                            <div class="card add_on_manager">
                                <div class="card-header ">
                                    <h5>{{ $addon->name }}</h5>
                                    <small class="text-muted">{{ $addon->description }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($addon->add_ons as $add_on)
                                            @if ($addon->name == 'Business Theme Addon')
                                                <div class="col-xxl-3 col-lg-4 col-md-4 col-sm-6 col-12">
                                                    <div class="product-card">
                                                        <div class="product-card-image">
                                                            <a href="#">
                                                                <img src="{{ $add_on->image }}" alt="">
                                                            </a>
                                                        </div>
                                                        <div class="product-card-content">
                                                            <div class="product-content-top">
                                                                <h3 class="h5"> {{ $add_on->name }}</h3>
                                                            </div>
                                                            <div class="product-content-bottom d-flex gap-2">
                                                                <a href="{{ $add_on->url }}"
                                                                    class="btn btn-primary border-0 w-50 text-capitalize"
                                                                    target="_blank">{{ __('View Details') }}</a>
                                                                <a href="{{ $add_on->demo_link }}"
                                                                    class="btn  btn-outline-primary w-50 text-capitalize"
                                                                    target="_blank">
                                                                    {{ __('View theme') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($addon->name == 'Appointment Addon')
                                                <div class="col-xxl-3 col-lg-4 col-md-4 col-sm-6 col-12">
                                                    <div class="product-card add_on_card">
                                                        <div class="card manager-card">
                                                            <div class="product-img">
                                                                <div class="theme-main">
                                                                    <div class="theme-avtar">
                                                                        <img src="{{ $add_on->image }}" alt=""
                                                                            class="img-user" style="max-width: 100%">
                                                                    </div>
                                                                </div>
                                                                <h5 class="text-capitalize mb-0"> {{ $add_on->name }}</h5>
                                                            </div>
                                                            <div class="product-content">
                                                                <div class="product_btn">
                                                                    <a href="{{ $add_on->demo_url }}"
                                                                        class="d-flex align-items-center justify-content-center gap-2 w-100"
                                                                        target="_new">
                                                                        <span class="btn-icon bg-primary">
                                                                            <svg width="9" height="10"
                                                                                viewBox="0 0 9 10" fill="none"
                                                                                xmlns="http://www.w3.org/2000/svg">
                                                                                <path
                                                                                    d="M8.875 4.99997C8.875 5.3733 8.41635 5.68101 8.32448 6.02518C8.22969 6.38101 8.46958 6.87684 8.28948 7.1882C8.10646 7.50466 7.55594 7.54257 7.29927 7.79924C7.0426 8.05591 7.00469 8.60643 6.68823 8.78945C6.37687 8.96955 5.88104 8.72966 5.52521 8.82445C5.18104 8.91632 4.87333 9.37497 4.5 9.37497C4.12667 9.37497 3.81896 8.91632 3.47479 8.82445C3.11896 8.72966 2.62313 8.96955 2.31177 8.78945C1.99531 8.60643 1.9574 8.05591 1.70073 7.79924C1.44406 7.54257 0.893542 7.50466 0.710521 7.1882C0.530417 6.87684 0.770312 6.38101 0.675521 6.02518C0.583646 5.68101 0.125 5.3733 0.125 4.99997C0.125 4.62664 0.583646 4.31893 0.675521 3.97476C0.770312 3.61893 0.530417 3.12309 0.710521 2.81174C0.893542 2.49528 1.44406 2.45737 1.70073 2.2007C1.9574 1.94403 1.99531 1.39351 2.31177 1.21049C2.62313 1.03039 3.11896 1.27028 3.47479 1.17549C3.81896 1.08362 4.12667 0.624969 4.5 0.624969C4.87333 0.624969 5.18104 1.08362 5.52521 1.17549C5.88104 1.27028 6.37687 1.03039 6.68823 1.21049C7.00469 1.39351 7.0426 1.94403 7.29927 2.2007C7.55594 2.45737 8.10646 2.49528 8.28948 2.81174C8.46958 3.12309 8.22969 3.61893 8.32448 3.97476C8.41635 4.31893 8.875 4.62664 8.875 4.99997Z"
                                                                                    fill="white" />
                                                                                <path
                                                                                    d="M5.76417 3.69621L4.09875 5.36163L3.23542 4.49902C3.04802 4.31163 2.74396 4.31163 2.55656 4.49902C2.36917 4.68642 2.36917 4.99048 2.55656 5.17788L3.76771 6.38902C3.95 6.57132 4.24604 6.57132 4.42833 6.38902L6.44229 4.37507C6.62969 4.18767 6.62969 3.88361 6.44229 3.69621C6.2549 3.50882 5.95156 3.50882 5.76417 3.69621Z"
                                                                                    fill="#6FD943" />
                                                                            </svg>
                                                                        </span>
                                                                        {{ __('Check Demo') }}
                                                                    </a>
                                                                    <a href="{{ $add_on->video_url }}"
                                                                        class="d-flex align-items-center justify-content-center gap-2 w-100"
                                                                        target="_new">
                                                                        <span class="btn-icon bg-primary">
                                                                            <svg width="11" height="10"
                                                                                viewBox="0 0 11 10" fill="none"
                                                                                xmlns="http://www.w3.org/2000/svg">
                                                                                <path
                                                                                    d="M2.91967 1.55665L4.36301 2.99998H1.86301C1.91402 2.69354 2.03619 2.40333 2.21971 2.15266C2.40322 1.90199 2.64296 1.69786 2.91967 1.55665ZM4.85967 1.33331H3.83301C3.76967 1.33331 3.70634 1.33665 3.64301 1.33998L5.30301 2.99998H6.52634L4.85967 1.33331ZM7.16634 1.33331H5.80634L7.47301 2.99998H9.13634C9.05825 2.534 8.81745 2.11082 8.45675 1.80565C8.09605 1.50049 7.63881 1.33313 7.16634 1.33331ZM4.99364 6.87531L6.66031 5.95865C6.71264 5.92993 6.7563 5.88768 6.78671 5.83631C6.81711 5.78494 6.83316 5.72634 6.83316 5.66665C6.83316 5.60695 6.81711 5.54835 6.78671 5.49698C6.7563 5.44561 6.71264 5.40336 6.66031 5.37465L4.99364 4.45798C4.94289 4.43007 4.88574 4.41586 4.82783 4.41676C4.76992 4.41766 4.71324 4.43363 4.66338 4.46311C4.61352 4.49258 4.57221 4.53454 4.5435 4.58484C4.51479 4.63515 4.49969 4.69206 4.49967 4.74998V6.58331C4.49969 6.64123 4.51479 6.69815 4.5435 6.74845C4.57221 6.79876 4.61352 6.84071 4.66338 6.87019C4.71324 6.89966 4.76992 6.91563 4.82783 6.91653C4.88574 6.91743 4.94289 6.90322 4.99364 6.87531ZM9.16634 3.66665V6.66665C9.16592 7.19695 8.95507 7.70541 8.58009 8.08039C8.20511 8.45537 7.69664 8.66622 7.16634 8.66665H3.83301C3.3027 8.66622 2.79424 8.45537 2.41926 8.08039C2.04428 7.70541 1.83343 7.19695 1.83301 6.66665V3.66665H9.16634Z"
                                                                                    fill="white" />
                                                                            </svg>
                                                                        </span>
                                                                        {{ __('Check Video') }}
                                                                    </a>
                                                                </div>
                                                                <a href="{{ $add_on->url }}"
                                                                    class="btn btn-primary w-100" target="_new">
                                                                    {{ __('View Details') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 ">
                                                    <div class="product-card">
                                                        <a href="{{ $add_on->url }}" target="_new">
                                                            <div class="manager-card p-3">
                                                                <div class="product-img">
                                                                    <div class="theme-main">
                                                                        <div class="theme-avtar">
                                                                            <img src="{{ $add_on->image }}"
                                                                                alt="" class="img-user"
                                                                                style="max-width: 100%">
                                                                        </div>
                                                                    </div>
                                                                    <h5 class="text-capitalize mb-0"> {{ $add_on->name }}</h5>
                                                                </div>
                                                                <div class="product-content">
                                                                    <button
                                                                        class="btn btn-primary w-100">{{ __('View Details') }}</button>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <div class="system-version">
        @php
            $version = config('verification.system_version');
        @endphp
        <h5 class="text-muted">{{ !empty($version) ? 'V' . $version : '' }}</h5>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on('click', '.module_change', function() {
            var id = $(this).attr('data-id');
            $('#form_' + id).submit();
        });

        if ($('#useradd-sidenav').length > 0) {
            var scrollSpy = new bootstrap.ScrollSpy(document.body, {
                target: '#useradd-sidenav',
                offset: 300
            })
        }
    </script>
@endpush
