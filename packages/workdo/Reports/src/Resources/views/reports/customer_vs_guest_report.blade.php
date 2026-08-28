@extends('layouts.main')

@section('page-title')
    {{ __('Customer Vs Guest Report') }}
@endsection

@section('page-breadcrumb')
    {{ __('Customer Vs Guest Report') }}
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

            // Ensure "Year" button is active and fetch data for "Year" by default
            $('.chart-data[value="year"]').addClass('active');
            fetchData('year');

            var arChart = chart([], [], []);

            function chart(customerCounts, guestCounts, Listdata) {
                var chartOptions = {
                    series: [{
                        name: 'Customer',
                        data: customerCounts
                    }, {
                        name: 'Guest',
                        data: guestCounts
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
                            text: 'Days'
                        }
                    },
                    colors: ['#ffa21d', '#FF3A6E'],
                    grid: {
                        strokeDashArray: 4,
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

                var arChart = new ApexCharts(document.querySelector("#chart-customer"), chartOptions);
                arChart.render();
                return arChart;
            }

            function fetchData(period, date = null) {
                $.ajax({
                    url: '{{ route('appointment.customer.guest.report') }}',
                    method: 'GET',
                    data: {
                        period: period,
                        date: date
                    },
                    success: function(response) {
                        arChart.updateSeries([{
                            name: 'Customer',
                            data: response.customerCounts
                        }, {
                            name: 'Guest',
                            data: response.guestCounts
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
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="chart-container" class="mt-4">
            <div class="card">
                <div class="card-body">
                    <div id="chart-customer" data-color="primary" data-height="400"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
