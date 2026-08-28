@extends('layouts.main')

@section('page-title')
    {{ __('Service Appointment Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Service Appointment Report') }}
@endsection

@push('scripts')
    <script src="{{ asset('packages/workdo/Reports/src/Resources/asset/js/apexcharts.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if ($(".datatable").length > 0) {
                new simpleDatatables.DataTable(".datatable");
            }

            select2();
            daterange();

            function daterange() {
                if ($("#pc-daterangepicker-1").length > 0) {
                    document.querySelector("#pc-daterangepicker-1").flatpickr({
                        mode: "range",
                        dateFormat: "d-m-Y"
                    });
                }
            }

            function select2() {
                if ($(".select2").length > 0) {
                    $($(".select2")).each(function(index, element) {
                        var id = $(element).attr('id');
                        var multipleCancelButton = new Choices(
                            '#' + id, {
                                removeItemButton: true,
                            }
                        );
                    });
                }
            }

            var arChart = chart([], []);

            function chart(serviceAppointmentCounts, monthList) {
                var options = {
                    chart: {
                        height: 500,
                        type: 'bar',
                        toolbar: {
                            show: false,
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },

                    series: serviceAppointmentCounts,
                    xaxis: {
                        categories: monthList,
                    },
                    colors: ['#DA7297', '#FFB4C2', '#EF9C66', '#BC5A94', '#538392', '#3AA6B9', '#808836',
                        '#FFBF00', '#D10363', '#DA7297', '#FFB4C2', '#EF9C66', '#BC5A94', '#538392',
                        '#3AA6B9', '#808836', '#FFBF00', '#D10363'
                    ],
                    fill: {
                        type: 'solid',
                    },

                    grid: {
                        strokeDashArray: 4,
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                    },
                    markers: {
                        size: 4,
                        colors: ['#DA7297', '#FFB4C2', '#EF9C66', '#BC5A94', '#538392', '#3AA6B9', '#808836',
                            '#FFBF00', '#D10363', '#DA7297', '#FFB4C2', '#EF9C66', '#BC5A94', '#538392',
                            '#3AA6B9', '#808836', '#FFBF00', '#D10363'
                        ],
                        opacity: 0.9,
                        strokeWidth: 2,
                        hover: {
                            size: 7,
                        }
                    }
                };

                var arChart = new ApexCharts(document.querySelector("#chart-service"), options);
                arChart.render();

                return arChart;
            }


            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('service.appointment.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        var serviceCounts = response.serviceCounts || [];
                        var listData = response.Listdata || [];
                        arChart.updateSeries(serviceCounts.map(service => ({
                            name: service.name,
                            data: service.data
                        })));

                        arChart.updateOptions({
                            xaxis: {
                                categories: listData
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            $('.chart-data').on('click', function() {
                $('.chart-data').removeClass('active');
                $(this).addClass('active');
                var period = $(this).val();
                fetchData(period);
            });

            $('#generateButton').on('click', function() {
                var date = $('#pc-daterangepicker-1').val();
                var period = 'between-date';
                fetchData(period, date);
            });

            // Trigger click event on "Year" button to load yearly data by default
            $('.chart-data[value="year"]').trigger('click');
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="card card-body">
                <ul class="gap-2 gap-3 nav nav-pills" id="pills-tab" role="tablist">
                    <li class="mb-2 nav-item">
                        <button class="nav-link chart-data active" name="chart_data"
                            value="year">{{ __('Year') }}</button>
                    </li>
                    <li class="mb-2 nav-item">
                        <button class="nav-link chart-data" name="chart_data"
                            value="last-month">{{ __('Last month') }}</button>
                    </li>
                    <li class="mb-2 nav-item">
                        <button class="nav-link chart-data" name="chart_data"
                            value="this-month">{{ __('This month') }}</button>
                    </li>
                    <li class="mb-2 nav-item">
                        <button class="nav-link chart-data" name="chart_data"
                            value="seven-day">{{ __('Last 7 days') }}</button>
                    </li>
                    <div class="mb-2 nav-item">
                        {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'date month-btn form-control', 'id' => 'pc-daterangepicker-1', 'placeholder' => 'DD-MM-YYYY']) }}
                    </div>
                    <div class="col-md-2" id="filter_type" style="padding-left :10px;">
                        <button class="btn btn-primary label-margin chart-data generate_button" id="generateButton"
                            name="chart_data" value="date-wise">{{ __('Generate') }}</button>
                    </div>
                    <div id="deals-staff-report" height="400" style="" width="1100" data-color="primary"
                        data-height="280"></div>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="chart-container">
            <div class="card">
                <div class="card-body">
                    <div class="scrollbar-inner">
                        <div id="chart-service" data-color="primary" data-height="400"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
