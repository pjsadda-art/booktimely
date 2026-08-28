@extends('layouts.main')

@section('page-title')
    {{ __('Appointment Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointment Report') }}
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

            $(".chart-data:first").addClass("active");

            var arChart = chart([], []);

            function chart(appointmentCounts, Listdata) {
                var chartBarOptions = {
                    series: [{
                        name: 'Appointment',
                        data: appointmentCounts
                    }],
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
                        categories: Listdata,
                        title: {
                            text: 'Months'
                        }
                    },
                    colors: ['#ffa21d', '#FF3A6E'],
                    grid: {
                        strokeDashArray: 4,
                    },
                    legend: {
                        show: true,
                        position: 'top',
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
                var arChart = new ApexCharts(document.querySelector("#chart-appointment"), chartBarOptions);
                arChart.render();
                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('appointment.data') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        arChart.updateSeries([{
                            data: response.appointmentCounts
                        }]);

                        arChart.updateOptions({
                            xaxis: {
                                categories: response.Listdata
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            // Function to handle click on chart data buttons
            $('.chart-data').on('click', function() {
                $('.chart-data').removeClass('active');
                $(this).addClass('active');
                var period = $(this).val();
                fetchData(period);
            });

            // Function to handle generate button click
            $('#generateButton').on('click', function() {
                var date = $('#pc-daterangepicker-1').val();
                var period = 'between-date';
                fetchData(period, date);
            });

            // Automatically load data for 'year' period on page load
            fetchData('year');
        });
    </script>

    {{-- Appointment By Location --}}
    <script>
        $(document).ready(function() {
            select2();
            daterange();

            var arChart = chart([], []);

            function chart(locationSeries, locationLabels) {
                var chartOptions = {
                    chart: {
                        height: 250,
                        type: 'donut',
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                            }
                        }
                    },
                    series: locationSeries,
                    labels: locationLabels,
                    colors: ['#FFD8D8', '#FFE1B6', '#94E7BF', '#E6E6E6', '#F4CDFA', '#CBD3FD', '#EEBFC0','#DBCDFB', '#C9D6DE', '#FFF5C1'],
                    legend: {
                        show: true
                    }
                };

                var arChart = new ApexCharts(document.querySelector("#appointmentLocation"), chartOptions);
                arChart.render();

                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('location.appointment.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        if (response.locationSeries && response.locationLabels) {
                            arChart.updateSeries(response.locationSeries);
                            arChart.updateOptions({
                                labels: response.locationLabels
                            });
                        } else {
                            
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            fetchData('year');

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

            function select2() {
                if ($(".select2").length > 0) {
                    $(".select2").each(function(index, element) {
                        var id = $(element).attr('id');
                        new Choices('#' + id, {
                            removeItemButton: true
                        });
                    });
                }
            }

            function daterange() {
                if ($("#pc-daterangepicker-1").length > 0) {
                    document.querySelector("#pc-daterangepicker-1").flatpickr({
                        mode: "range",
                        dateFormat: "d-m-Y"
                    });
                }
            }
        });
    </script>

    {{-- Appointment By Staff --}}
    <script>
        $(document).ready(function() {
            select2();
            daterange();
            
            var arChart = chart([], []);
            
            function chart(staffSeries, staffLabels) {
                var chartOptions = {
                    chart: {
                        height: 250,
                        type: 'donut',
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                            }
                        }
                    },
                    series: staffSeries,
                    labels: staffLabels,
                    colors: ['#FFD8D8', '#FFE1B6', '#94E7BF', '#E6E6E6', '#F4CDFA', '#CBD3FD', '#EEBFC0','#DBCDFB', '#C9D6DE', '#FFF5C1'],
                    legend: {
                        show: true
                    }
                };

                var arChart = new ApexCharts(document.querySelector("#appointmentStaff"), chartOptions);
                arChart.render();

                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('staff.appointment.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        if (response.staffSeries && response.staffLabels) {
                            arChart.updateSeries(response.staffSeries);
                            arChart.updateOptions({
                                labels: response.staffLabels
                            });
                        } else {
                           
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            fetchData('year');

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

            function select2() {
                if ($(".select2").length > 0) {
                    $(".select2").each(function(index, element) {
                        var id = $(element).attr('id');
                        new Choices('#' + id, {
                            removeItemButton: true
                        });
                    });
                }
            }

            function daterange() {
                if ($("#pc-daterangepicker-1").length > 0) {
                    document.querySelector("#pc-daterangepicker-1").flatpickr({
                        mode: "range",
                        dateFormat: "d-m-Y"
                    });
                }
            }
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="card card-body">
                <ul class="nav nav-pills gap-2 gap-3" id="pills-tab" role="tablist">
                    <li class="nav-item mb-2">
                        <button class="nav-link chart-data" name="chart_data" value="year">{{ __('Year') }}</button>
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
                    <div id="deals-staff-report" height="400" style="" width="1100" data-color="primary"
                        data-height="280">
                    </div>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="chart-container">
            <div class="card">
                <div class="card-body">
                    <div class="scrollbar-inner">
                        <div id="chart-appointment" data-color="primary" data-height="400"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Most Appointment locations') }}</h5>
                </div>
                <div class="card-body">
                    <div id="appointmentLocation"></div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Most Appointment staffs') }}</h5>
                </div>
                <div class="card-body">
                    <div id="appointmentStaff"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
