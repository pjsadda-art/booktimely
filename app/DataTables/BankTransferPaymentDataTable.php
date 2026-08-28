<?php

namespace App\DataTables;

use App\Models\BankTransferPayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BankTransferPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {    $query->with('user');

        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('user_id', function (BankTransferPayment $bank_transfer_payment) {
                return !empty($bank_transfer_payment->user) ? $bank_transfer_payment->user->name : '';
            })
            ->filterColumn('user_id', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('name', function (BankTransferPayment $bank_transfer_payment) {
                return !empty($bank_transfer_payment->user) ? $bank_transfer_payment->user->name : '';
            })
            ->addColumn('created_at', function (BankTransferPayment $bank_transfer_payment) {
                return company_datetime_formate($bank_transfer_payment->created_at);
            })
            ->orderColumn('created_at', function ($query, $bank_transfer_payment) {
                $query->orderBy('created_at', $bank_transfer_payment);
            })
            ->editColumn('status', function (BankTransferPayment $bank_transfer_payment) {
                $status = ucfirst($bank_transfer_payment->status);
                $statusClass = '';
                switch ($bank_transfer_payment->status) {
                    case 'Approved':
                        $statusClass = 'bg-success';
                        break;
                    case 'Pending':
                        $statusClass = 'bg-warning';
                        break;
                    default:
                        $statusClass = 'bg-danger';
                        break;
                }
                return '<span class="pro-badges badge fix_badge ' . $statusClass . ' p-2 px-3 text-white">' .  ucfirst($status) . '</span>';
            })->filterColumn('status', function ($query, $keyword) {
                $keyword = ucfirst(strtolower($keyword));
                $query->where('status', $keyword);
            })
            ->orderColumn('status', function ($query, $bank_transfer_payment) {
                $query->orderBy('status', $bank_transfer_payment);
            })
            ->editColumn('price', function (BankTransferPayment $bank_transfer_payment) {
                return super_currency_format_with_sym($bank_transfer_payment->price);

            })->filterColumn('price', function ($query, $keyword) {
                $query->whereRaw("CONCAT(bank_transfer_payments.price, ' ', bank_transfer_payments.price_currency) like ?", ["%{$keyword}%"]);
            })
            ->orderColumn('price', function ($query, $bank_transfer_payment) {
                $query->orderBy('price', $bank_transfer_payment);
            })
            ->editColumn('attachment', function (BankTransferPayment $bank_transfer_payment) {
                $html = '';
                if (!empty($bank_transfer_payment->attachment) && check_file($bank_transfer_payment->attachment)) {
                    $fileUrl = get_file($bank_transfer_payment->attachment);
                    $html .= '<div class="d-flex"><div class="action-btn me-2"><a class="btn btn-sm bg-primary align-items-center" data-bs-toggle="tooltip"
                    data-bs-original-title="Download" href="' . $fileUrl . '" download><i class="ti ti-download text-white"></i></a></div> <div class="action-btn "><a class="btn btn-sm bg-secondary  align-items-center" href="' . $fileUrl . '" target="_blank"><i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="' . __('Preview') . '"></i></a></div></div>';
                } else {
                    $html .= '<p>-</p>';
                }
                return $html;
            })
            ->addColumn('action', function (BankTransferPayment $bank_transfer_payment) {
                return  view('bank_transfer.dataTable', compact('bank_transfer_payment'));
            })
            ->rawColumns(['id', 'attachment', 'name', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(BankTransferPayment $model): QueryBuilder
    {
        return $model->newquery()->with('user')->where('type', 'plan');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('BankTransferPayment-table')
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
        return [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('order_id')->title('Order Id'),
            Column::make('created_at')->title('Date'),
            Column::make('user_id')->title('Name'),
            Column::make('price')->title('Price'),
            Column::make('status')->title('Status'),
            Column::make('attachment')->title('Attachment')->exportable(false)->printable(false)->searchable(false)->orderable(false),
            Column::computed('action')->exportable(false)->printable(false)->width(60),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'BankTransferPayment_' . date('YmdHis');
    }
}
