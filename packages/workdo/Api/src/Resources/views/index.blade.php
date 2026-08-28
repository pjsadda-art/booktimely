@permission('api manage')
    @php
        $company_settings = getCompanyAllSetting();

        $favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : 'uploads/logo/favicon.png');
    @endphp
    <!DOCTYPE html>
    <html lang="en"
        dir="{{ isset($company_settings['site_rtl']) && $company_settings['site_rtl'] == 'on' ? 'rtl' : '' }}">

    <head>
        <title>{{ config('app.name', 'Laravel') }} | {{ __('Api Docs') }}</title>

        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0,
      user-scalable=0, minimal-ui" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- Favicon icon -->
        {{--  <link rel="icon" href="{{ get_file(favicon()) }}{{ '?' . time() }}" type="image/x-icon" />  --}}
        <link rel="icon"
            href="{{ check_file($favicon) ? get_file($favicon) : get_file('uploads/logo/favicon.png') }}{{ '?' . time() }}"
            type="image/x-icon" />
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">

        <!-- font css -->
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

        <!-- vendor css -->
        @if ((isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off') == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}" />
        @if ((isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off') == 'on')
            <link rel="stylesheet" href="{{ asset('packages/workdo/Api/src/Resources/assets/css/custom-rtl.css') }}" />
        @else
            <link rel="stylesheet" href="{{ asset('packages/workdo/Api/src/Resources/assets/css/custom.css') }}" />
        @endif


        <style>
            .b-brand {
                height: 100%;
                width: 100%;
                display: block;
                max-width: 135px;
            }
            .dash-sidebar.light-sidebar.api-sidebar{
                box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
                background: #fff;
            }
            .api-dash-container{
                top: 0;

            }
            .dash-container.api-dash-container .custom-content .content-inner{
                padding:20px 40px;
            }
            .dash-sidebar.light-sidebar.api-sidebar .dash-link{
                margin: 0 !important;
                padding: 10px 15px !important ;
                border-radius: 0 !important;
            }
            .dash-sidebar.light-sidebar.api-sidebar .dash-navbar > .dash-item:hover > .dash-link,
            .dash-sidebar.light-sidebar.api-sidebar .dash-navbar > .dash-item.dash-trigger > .dash-link{
                background-color:  #e3e8ee;
            }
            .dash-container .custom-content .content-inner .row {
                margin: 0 -15px;
            }
            .api-info-row:not(:last-of-type){
                border-bottom: 1px solid var(--bs-border-color);
                margin-bottom: 20px !important;
            }
            .row-gaps{
                row-gap: 25px;
            }

            @media screen and (max-width: 1024px) {
            .api-dash-container{
                border-top: 1px solid var(--bs-border-color)
            }
            .dash-container.api-dash-container .custom-content .content-inner{
                padding:20px 15px;
            }
        }
        </style>
    </head>

    <body>
        <!-- [ Pre-loader ] start -->
        <div class="loader-bg">
            <div class="loader-track">
                <div class="loader-fill"></div>
            </div>
        </div>
        <!-- [ Pre-loader ] End -->
        <div class="me-auto dash-mob-drp api-hamburger">
            <ul class="list-unstyled">
                <li class="dash-h-item mob-hamburger">
                    <a href="#!" class="dash-head-link" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <nav class="dash-sidebar light-sidebar transprent-bg custom-sidebar api-sidebar">
            <div class="navbar-wrapper">
                <div class="m-header">
                    <a href="{{ url('/') }}" class="b-brand">
                        <!-- ========   change your logo hear   ============ -->
                        <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt=""
                            class="logo logo-lg" />
                        <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt=""
                            class="logo logo-sm" />
                    </a>
                </div>
                <div class="navbar-content">
                    <ul class="dash-navbar">
                        <li class="dash-item dash-hasmenu">
                            <a href="#introduction" class="dash-link">
                                <span class="dash-mtext">{{ __('Introduction')}}</span>
                            </a>
                        </li>
                        @foreach ($fileNames as $fileName)
                            <li class="dash-item dash-hasmenu">
                                <a class="dash-link">
                                    <span class="dash-mtext">{{ ucFirst($fileName) }}</span>
                                    <span class="dash-arrow">
                                        <svg height="16" viewBox="0 0 16 16" width="16"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.591 5.293a1 1 0 0 1 1.416 1.416l-6.3 6.3a1 1 0 0 1-1.414 0l-6.3-6.3A1 1 0 0 1 2.41 5.293L8 10.884z"
                                                fill-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                </a>
                                <ul class="dash-submenu">
                                    @foreach ($contents[$fileName] as $key => $value)
                                        <li class="dash-item">
                                            <a href="#{{ $key }}" class="dash-link d-flex">
                                                @if ($value->method == 'GET')
                                                    <span class="text-blue me-2">{{ $value->method }}</span>
                                                @elseif ($value->method == 'POST')
                                                    <span class="text-success me-2">{{ $value->method }}</span>
                                                @elseif($value->method == 'PUT')
                                                    <span class="text-warning me-2">{{ $value->method }}</span>
                                                @elseif($value->method == 'DELETE')
                                                    <span class="text-danger me-2">{{ $value->method }}</span>
                                                @endif
                                                <span>{{ ucFirst(str_replace('-', ' ', $key)) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>
        <div class="dash-container dash-custom-content">
            <div class="dash-content custom-content">
                <div class="page-main pt-2">
                    <div class="page-main-content">
                        <div id="introduction" class="content-inner">
                            <div class="content-start">
                                <h2 class="mb-4">{{ __('Introduction') }}</h2>
                                <div class="row row-gaps">
                                    <div class="col-md-6">
                                        <p>
                                            {{ __('Are you ready to unlock the full potential of BookingGo and seamlessly integrate it into your applications? Look no further. This comprehensive API documentation is your gateway to harnessing the power of BookingGo\'s features, allowing you to streamline your work, boost productivity, and bring your projects to the next level.') }}
                                        </p>
                                        <h4 class="border-bottom pb-2 mb-3">{{ __('Getting Started') }}</h4>
                                        <p>
                                            {{ __('This API documentation is a resource for developers, both seasoned and new, who want to leverage BookingGo\'s capabilities within their applications. Here\'s what you can expect to find') }}
                                        </p>
                                        <h4 class="border-bottom pb-2 mb-3">{{ __('Endpoints') }}</h4>
                                        <ul>
                                            <li>
                                                {{ __('GET') }}
                                            </li>
                                            <li>
                                                {{ __('POST') }}
                                            </li>
                                            <li>
                                                {{ __('PUT') }}
                                            </li>
                                            <li>
                                                {{ __('DELETE') }}
                                            </li>
                                        </ul>
                                        <p>
                                            {{ __('You can use HTTP methods like GET to retrieve data, POST to create new data, PUT to update existing data, and DELETE to remove data.') }}
                                        </p>
                                        <h4 class="border-bottom pb-2 mb-3">{{ __('Request Parameters') }}</h4>
                                        <p>{{ __('Request parameters are used to customize the behavior of your API requests and provide more specific details when interacting with the API. For BookingGo, you might have the following types of request parameters:') }}
                                        </p>
                                        <ul>
                                            <li>
                                                <b>{{ __('Query Parameters : ') }}</b>{{ __('You can use query parameters to filter or sort data when retrieving information. For example, you could use id to filter data within a specific id number.') }}
                                            </li>
                                            <li>
                                                <b>{{ __('Request Headers : ') }}</b>{{ __('Headers can be used for authentication and additional metadata about the request. For instance, you might need to include an Authorization header with an Bearer token for authentication.') }}
                                            </li>
                                            <li>
                                                <b>{{ __('Request Body : ') }}</b>{{ __('When creating or updating data in BookingGo, you\'ll typically send a JSON request body. The request body will contain the data to be created or updated, such as users information, project information, or invoice information.') }}
                                            </li>
                                        </ul>
                                        <h4 class="border-bottom pb-2 mb-3">{{ __('Response Format') }}</h4>
                                        <p>{{ __('BookingGo\'s API typically responds with data in JSON format') }}</p>
                                        <ul>
                                            <li>
                                                <b>{{ __('Status Code : ') }}</b>{{ __('The response typically includes an HTTP status code indicating the outcome of the request (e.g., 200 for success, 404 for not found, 401 for unauthorized).') }}
                                            </li>
                                            <li>
                                                <b>{{ __('Data : ') }}</b>{{ __('The actual data is contained within the "data" field.') }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="api-request-example mb-4">
                                            <div class="api-request-example-topbar">
                                                <div class="api-request-example-title">Base URL</div>
                                            </div>
                                            <div class="api-request-example-bottom">
                                                <code>https://example.com</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ($fileNames as $fileName)
                            <div class="content-inner">
                                <div class="content-start">
                                    <h2 class="mb-4">{{ ucFirst($fileName) }}</h2>
                                    @foreach ($contents[$fileName] as $key => $apiDetail)
                                        <div class="row row-gaps api-info-row" id="{{ $key }}">
                                            <div class="col-md-6">
                                                <h4 class="mb-4">
                                                    {{ ucFirst($fileName) . ' - ' . ucFirst(str_replace('-', ' ', $key)) }}
                                                </h4>
                                                <div class="your-key-box pb-0 mb-3">
                                                    <div
                                                        class="key-box-topbar codecopy-topbar d-flex justify-content-between align-items-center ps-3 pb-2">
                                                        <h6>{{ __('Endpoints') }}</h6>
                                                        <div class="codecopy-top-right d-flex align-items-start px-2">
                                                            <div class="copy-opc">
                                                                <button class="ClickToCopy btn p-0 ps-2"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Click To copy') }}">
                                                                    <i class="ti ti-clipboard"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="key-box-main">
                                                        @if ($apiDetail->method == 'GET')
                                                            <span class="text-blue">{{ $apiDetail->method }}</span>
                                                        @elseif ($apiDetail->method == 'POST')
                                                            <span class="text-success">{{ $apiDetail->method }}</span>
                                                        @elseif($apiDetail->method == 'PUT')
                                                            <span class="text-warning">{{ $apiDetail->method }}</span>
                                                        @elseif($apiDetail->method == 'DELETE')
                                                            <span class="text-danger">{{ $apiDetail->method }}</span>
                                                        @endif
                                                        <span
                                                            class="text-green copy-content">{{ $apiDetail->endpoints }}</span>
                                                    </div>
                                                </div>
                                                <div class="your-key-box pb-0 mb-3">
                                                    <div
                                                        class="key-box-topbar codecopy-topbar d-flex justify-content-between align-items-center ps-3 pb-2">
                                                        <h6>{{ __('Headers') }}</h6>
                                                        <div class="codecopy-top-right d-flex align-items-start px-2">
                                                            <div class="copy-opc">
                                                                <button class="ClickToCopy btn p-0 ps-2"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Click To copy') }}">
                                                                    <i class="ti ti-clipboard"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="code-main m-2">
                                                        <p class="mb-0">
                                                            <pre class="text-blue copy-content">@json($apiDetail->header, JSON_PRETTY_PRINT)</pre>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="your-key-box pb-0 mb-3">
                                                    <div
                                                        class="key-box-topbar codecopy-topbar d-flex justify-content-between align-items-center ps-3 pb-2">
                                                        <h6>{{ __('Parameter') }}</h6>
                                                        <div class="codecopy-top-right d-flex align-items-start px-2">
                                                            <div class="copy-opc">
                                                                <button class="ClickToCopy btn p-0 ps-2"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Click To copy') }}">
                                                                    <i class="ti ti-clipboard"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="code-main m-2">
                                                        <p class="mb-0">
                                                            <pre class="text-blue copy-content">@json($apiDetail->request, JSON_PRETTY_PRINT)</pre>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="code-copy-box code-copy-box-dark pt-2 pb-0 mb-3">
                                                    <div
                                                        class="codecopy-topbar d-flex justify-content-between align-items-center ps-3 pb-2">
                                                        <h6 class="mb-0">{{ __('Success Response') }}</h6>
                                                        <div class="codecopy-top-right d-flex align-items-start px-2">
                                                            <div class="copy-opc">
                                                                <button class="ClickToCopy btn p-0 ps-2 text-white"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Click To copy') }}">
                                                                    <i class="ti ti-clipboard"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="codecopy-code d-flex pe-3">
                                                        <div class="codecopy-line px-3">
                                                            @php
                                                                $lines = explode(PHP_EOL, json_encode($apiDetail->success_response, JSON_PRETTY_PRINT));
                                                            @endphp
                                                            @for ($i = 1; $i <= count($lines); $i++)
                                                                <div>{{ $i }}</div>
                                                            @endfor
                                                        </div>
                                                        <div class="code-main">
                                                            <p class="mb-0">
                                                                <span
                                                                    class="text-green">{{ $apiDetail->status_code->success }}</span>
                                                                <pre class="text-blue copy-content">@json($apiDetail->success_response, JSON_PRETTY_PRINT)</pre>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="code-copy-box code-copy-box-dark pt-2 pb-0 mb-3">
                                                    <div
                                                        class="codecopy-topbar d-flex justify-content-between align-items-center ps-3 pb-2">
                                                        <h6 class="mb-0">{{ __('Error Response') }}</h6>
                                                        <div class="codecopy-top-right d-flex align-items-start px-2"
                                                            data-bs-toggle="tooltip" title="{{ __('Click To copy') }}">
                                                            <div class="copy-opc">
                                                                <button class="ClickToCopy btn p-0 ps-2 text-white">
                                                                    <i class="ti ti-clipboard"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="codecopy-code d-flex pe-3">
                                                        <div class="codecopy-line px-3">
                                                            @php
                                                                $errorlines = explode(PHP_EOL, json_encode($apiDetail->error_response, JSON_PRETTY_PRINT));
                                                            @endphp
                                                            @for ($i = 1; $i <= count($errorlines) + 1; $i++)
                                                                <div>{{ $i }}</div>
                                                            @endfor
                                                        </div>
                                                        <div class="code-main">
                                                            <p class="mb-0">
                                                                <span
                                                                    class="text-danger">{{ $apiDetail->status_code->error }}</span>
                                                                <pre class="text-blue copy-content">@json($apiDetail->error_response, JSON_PRETTY_PRINT)</pre>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>



        <!-- [ Main Content ] end -->



        <!-- Required Js -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/dash.js') }}"></script>

        <script>
            $(document).on('click', '.ClickToCopy', function() {

                var textToCopy = $(this).parent().parent().parent().parent().find('.copy-content').text();

                var tempTextArea = document.createElement("textarea");
                tempTextArea.value = textToCopy;
                document.body.appendChild(tempTextArea);

                tempTextArea.select();
                document.execCommand('copy');

                document.body.removeChild(tempTextArea);
                $(this).text('copied');
                setTimeout(function() {
                    $(".ClickToCopy").html('<i class="ti ti-clipboard"></i>');
                }, 1000);
            });
        </script>
    </body>

    </html>
@endpermission
