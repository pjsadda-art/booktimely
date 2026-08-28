<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['coupon', 'receipt', 'created_at', 'price', 'payment_status'];

        $dataTable = (new EloquentDataTable($query))->addIndexColumn()
            ->addColumn('created_at', fn(Order $order) => company_datetime_formate($order->created_at))
            ->orderColumn('created_at', fn($query, $order) => $query->orderBy('created_at', $order))
            ->editColumn('price', function (Order $order) {
                return super_currency_format_with_sym($order->price);
            })
            ->filterColumn('price', fn($query, $keyword) =>
                $query->whereRaw("CONCAT(orders.price, ' ', orders.price_currency) LIKE ?", ["%{$keyword}%"])
            )
            ->orderColumn('price', fn($query, $order) => $query->orderBy('price', $order))
            ->addColumn('payment_status', function (Order $order) {
                return '<span class="pro-badges badge fix_badges ' . ($order->payment_status == 'succeeded' ? 'bg-success' : 'bg-danger') .
                    '  p-2 px-3 text-white">' . ucfirst($order->payment_status) . '</span>';
            })
            ->orderColumn('payment_status', fn($query, $order) => $query->orderBy('payment_status', $order))
            ->filterColumn('payment_status', fn($query, $keyword) =>
                $query->where('orders.payment_status', 'LIKE', "%{$keyword}%")
            )
            ->addColumn('coupon', fn(Order $order) =>
                !empty($order->total_coupon_used) ?
                ($order->total_coupon_used->coupon_detail->code ?? '-') : '-'
            )
            ->editColumn('receipt', fn(Order $order) => $this->getReceiptHtml($order));

        // Fetch the plan once
        $plan = Plan::where('is_free_plan', 1)->first();

        // Fetch the latest orders for all users
        $latestOrders = \App\Models\Order::whereIn('user_id', $query->pluck('user_id'))
            ->select('user_id', \DB::raw('MAX(id) as latest_order_id'))
            ->groupBy('user_id')
            ->pluck('latest_order_id', 'user_id');

        if (Auth::user()->type == 'super admin') {
            $rowColumn[] = 'refund';
            $dataTable->addColumn('refund', function (Order $order) use ($plan, $latestOrders) {
                $isLatestOrder = $latestOrders[$order->user_id] ?? null;
                $html = '<td class="text-center">';
                if ($isLatestOrder == $order->id &&
                    $order->plan_id != $plan->id &&
                    in_array($order->payment_status, ['succeeded', 'Approval', 'success']) ||
                    $order->payment_type == 'Bank Transfer') {
                    $html .= '<div class="btn bg-primary">
                        <a href="' . route('order.refund', $order->user_id) . '"
                        title="' . __('Payment Refund') . '" data-bs-toggle="tooltip"
                        data-title="' . __('Payment Refund') . '"
                        class="d-inline align-items-center text-white">' . __('Refund') . '</a></div>';
                } else {
                    $html .= '-';
                }
                $html .= '</td>';
                return $html;
            });
        }

        return $dataTable->rawColumns($rowColumn);
    }

    private function getReceiptHtml(Order $order): string
    {
        if (isset($order->receipt) && !empty($order->receipt) && $order->receipt != 'free coupon' &&  in_array($order->payment_type, ['STRIPE', 'Fatora','CoinPayments'])) {
            return '<a href="' . $order->receipt . '" data-bs-toggle="tooltip"
                    data-bs-original-title="' . __('Invoice') . '" target="_blank">
                    <i class="ti ti-file-invoice text-primary"></i></a>';
        } elseif ($order->payment_type == 'Bank Transfer') {
            return '<a href="' . (!empty($order->receipt) && check_file($order->receipt) ?
                    get_file($order->receipt) : '#!') . '" data-bs-toggle="tooltip"
                    data-bs-original-title="' . __('Invoice') . '" target="_blank">
                    <i class="ti ti-file-invoice text-primary"></i></a>';
        } elseif ($order->receipt == 'free coupon') {
            return '<p>' . __('Used 100 % discount coupon code.') . '</p>';
        } elseif ($order->payment_type == 'Manually') {
            return '<p>' . __('Manually plan upgraded by super admin') . '</p>';
        }
        return '-';
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        $user = Auth::user();
        return $model->with('User')
            ->where(function ($query) use ($user) {
                if ($user->type != 'super admin') {
                    $query->where('orders.user_id', $user->id);
                }
            });
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('plan-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var name = $("input[name=name]").val();
                    d.name = name

                    var role = $("select[name=role]").val();
                    d.role = role
                }',
            ])
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                $("body").on("click", "#applyfilter", function() {

                    if (!$("input[name=name]").val() && !$("select[name=role]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#users-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=name]").val("")
                    $("select[name=role]").val("")
                    $("#users-table").DataTable().draw();
                });

                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('order_id')->title(__('Order Id')),
            Column::make('created_at')->title(__('Date')),
            Column::make('name')->title(__('Name')),
            Column::make('plan_name')->title(__('Plan Name')),
            Column::make('price')->title(__('Price')),
            Column::make('payment_type')->title(__('Payment Type')),
            Column::make('payment_status')->title(__('Status')),
            Column::computed('coupon')->title(__('Coupon')),
            Column::make('receipt')->title(__('Invoice'))->searchable(false)->exportable(false)->printable(false),
        ];
        if (Auth::user()->type == 'super admin') {
            $columns[] = Column::computed('refund')->title(__('Refund'))->searchable(false)->exportable(false)->printable(false);
        }
        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Order_' . date('YmdHis');
    }
}
