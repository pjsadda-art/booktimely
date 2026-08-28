@extends('layouts.main')

@section('page-title')
    {{ __('Revenue Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Revenue Report') }}
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
                        new Choices(
                            '#' + id, {
                                removeItemButton: true,
                            }
                        );
                    });
                }
            }

            $(".chart-data:first").addClass("active");
            var arChart = chart([], []);

            function chart(revenueCounts, Listdata) {
                var chartBarOptions = {
                    series: [{
                        name: 'Revenue',
                        data: revenueCounts,
                    }],
                    chart: {
                        height: 400,
                        type: 'bar',
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '40%',
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 5,
                        colors: ['transparent']
                    },
                    title: {
                        text: '',
                        align: 'left'
                    },
                    xaxis: {
                        categories: Listdata,
                        labels: {
                            align: 'left'
                        },
                        title: {
                            text: 'Months'
                        }
                    },
                    colors: ['#ffa21d', '#FF3A6E', '#FF5733', '#C70039', '#900C3F', '#581845'],
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                    },
                    yaxis: {
                        title: {
                            text: 'Revenue'
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "$" + val
                            }
                        }
                    }
                };

                var arChart = new ApexCharts(document.querySelector("#chart-revenue"), chartBarOptions);
                arChart.render();

                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('reveneue.appointment.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        if (Array.isArray(response.revenueCounts) && Array.isArray(response.Listdata)) {
                            if (arChart) {
                                arChart.updateSeries([{
                                    data: response.revenueCounts
                                }]);

                                arChart.updateOptions({
                                    xaxis: {
                                        categories: response.Listdata
                                    }
                                });
                            } else {
                                console.error('arChart is not defined.');
                            }
                        } else {
                            console.error('Invalid data format:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            // Trigger fetchData for "year" period on page load
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
        });
    </script>

    {{-- Revenue By Location --}}
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

                var arChart = new ApexCharts(document.querySelector("#revenulocation"), chartOptions);
                arChart.render();

                return arChart;
            }
 
            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('location.revenue.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        if (response.locationSeries && response.locationLabels) {
                            var currencySymbol = @json($company_settings['defult_currancy_symbol']);
                            arChart.updateSeries(response.locationSeries);
                            arChart.updateOptions({
                                labels: response.locationLabels,
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return currencySymbol +
                                            val;
                                        }
                                    }
                                }
                            });
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

    {{--  Revenue By Staff --}}
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

                var arChart = new ApexCharts(document.querySelector("#revenustaff"), chartOptions);
                arChart.render();

                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('staff.revenue.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        if (response.staffSeries && response.staffLabels) {
                            var currencySymbol = @json($company_settings['defult_currancy_symbol']);
                            arChart.updateSeries(response.staffSeries);
                            arChart.updateOptions({
                                labels: response.staffLabels,
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return currencySymbol +
                                            val;
                                        }
                                    }
                                }
                            });
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
                        {{ Form::text('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'date month-btn form-control', 'id' => 'pc-daterangepicker-1', 'placeholder' => 'DD-MM-YYYY']) }}
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
                        <div id="chart-revenue" data-color="primary" data-height="400"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Most Earning Locations') }}</h5>
                </div>
                <div class="card-body">
                    <div id="revenulocation"></div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Most Earning Staffs') }}</h5>
                </div>
                <div class="card-body">
                    <div id="revenustaff"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
