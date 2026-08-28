@extends('layouts.main')

@section('page-title')
    {{ __('Appointment Status Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointment Status Report') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Reports/src/Resources/asset/css/main-style.css') }}">
@endpush

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
                    $("#pc-daterangepicker-1").flatpickr({
                        mode: "range",
                        dateFormat: "d-m-Y"
                    });
                }
            }

            function select2() {
                if ($(".select2").length > 0) {
                    $(".select2").each(function(index, element) {
                        var id = $(element).attr('id');
                        new Choices('#' + id, {
                            removeItemButton: true,
                        });
                    });
                }
            }

            var arChart = chart([], []);

            function chart(series, categories) {
                var chartBarOptions = {
                    series: series,
                    chart: {
                        height: 400,
                        type: 'area',
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 0.2
                        },
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },
                    title: {
                        text: '',
                        align: 'left'
                    },
                    xaxis: {
                        categories: categories,
                        title: {
                            text: 'Months'
                        }
                    },
                    colors: ['#20c997', '#3ec9d6', '#ffa21d', '#6f42c1', '#ff3a6e'],
                    fill: {
                        type: 'gradient', // Added gradient fill
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.9,
                            stops: [0, 90, 100]
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1', // Added grid border color
                        // strokeDashArray: 4,
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        onItemClick: {
                            toggleDataSeries: false
                        },
                        onItemHover: {
                            highlightDataSeries: false
                        },
                        markers: {
                            width: 10,
                            height: 10,
                            strokeWidth: 0,
                            strokeColor: '#fff',
                            fillColors: undefined,
                            radius: 12,
                            onClick: undefined,
                            offsetX: 0,
                            offsetY: 0
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Appointments'
                        }
                    }
                };
                var arChart = new ApexCharts(document.querySelector("#chart-status"), chartBarOptions);
                arChart.render();
                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('appointment.status.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        var series = [];
                        for (var status in response.statusData) {
                            series.push({
                                name: status,
                                data: response.statusData[status]
                            });
                        }

                        arChart.updateSeries(
                            series.map(s => ({
                                name: s.name,
                                data: Array.isArray(s.data) ? s.data :
                                [] // Ensure it's an array
                            }))
                        );


                        arChart.updateOptions({
                            xaxis: {
                                categories: response.categories
                            }
                        });

                        updateHtmlContent(response.statusData, response.statuses);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            function updateHtmlContent(statusData, statuses) {
                var htmlContent = '';
                for (var status in statusData) {
                    // Ensure the data for each status is an array, or default to an empty array
                    var statusArray = Array.isArray(statusData[status]) ? statusData[status] : [];

                    // Safely reduce the array to calculate total count
                    var totalCount = statusArray.reduce((a, b) => a + b, 0);

                    var foundStatus = statuses.find(s => s.title === status);
                    var statusColor = foundStatus ? foundStatus.status_color : '5bc0de';

                    htmlContent += `
        <div class="col-xxl-3 col-md-4 col-sm-6 col-12">
            <div class="card appointment-status-card">
                <div class="card-header appointment-card-inner d-flex align-items-center gap-3">
                    <div class="btn" style="background-color: #${statusColor};">
                        <i class="ti ti-tag"></i>
                    </div>
                    <div class="appointment-content">
                        <span>${status}</span>
                        <h2>${totalCount}</h2>
                    </div>
                </div>
            </div>
        </div>`;
                }
                $('#status-summary').html(htmlContent);
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

            fetchData('year');
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="card card-body">
                <ul class="nav nav-pills gap-2 gap-3" id="pills-tab" role="tablist">
                    <li class="nav-item mb-2">
                        <button class="nav-link chart-data active" name="chart_data"
                            value="year">{{ __('Year') }}</button>
                    </li>
                    <li class="nav-item mb-2">
                        <button class="nav-link chart-data" name="chart_data"
                            value="last-month">{{ __('Last month') }}</button>
                    </li>
                    <li class="nav-item mb-2">
                        <button class="nav-link chart-data" name="chart_data"
                            value="this-month">{{ __('This month') }}</button>
                    </li>
                    <li class="nav-item mb-2">
                        <button class="nav-link chart-data" name="chart_data"
                            value="seven-day">{{ __('Last 7 days') }}</button>
                    </li>
                    <div class="nav-item mb-2">
                        {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'date month-btn form-control', 'id' => 'pc-daterangepicker-1', 'placeholder' => 'DD-MM-YYYY']) }}
                    </div>
                    <div class="col-md-2" id="filter_type" style="padding-left :10px;">
                        <button class="btn btn-primary label-margin chart-data generate_button" id="generateButton"
                            name="chart_data" value="date-wise">{{ __('Generate') }}</button>
                    </div>
                </ul>
            </div>
        </div>
    </div>
    <div class="row" id="status-summary">
        @foreach ($statuses as $status)
            <div class="col-xxl-3 col-md-4 col-sm-6 col-12">
                <div class="card appointment-status-card">
                    <div class="card-header appointment-card-inner d-flex align-items-center gap-3">
                        <div class="btn"
                            style="background-color: #{{ !empty($status->status_color) ? $status->status_color : '5bc0de' }};">
                            <i class="ti ti-tag"></i>
                        </div>
                        <div class="appointment-content">
                            <span>{{ $status->title }}</span>
                            <h2>{{ array_sum($statusCounts[$status->title] ?? []) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-12" id="chart-container">
            <div class="card">
                <div class="card-body">
                    <div class="scrollbar-inner">
                        <div id="chart-status" data-color="primary" data-height="400"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
