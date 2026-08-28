<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['avatar', 'name', 'email','role'];
        $dataTable = (new EloquentDataTable($query))
        ->editColumn('avatar', function (Customer $customer) {
            $avatarUrl = isset($customer->customer) && $customer->customer->avatar ? get_file($customer->customer->avatar) : get_file('uploads/users-avatar/avatar.png');
            return '<a><img src="' . $avatarUrl . '" class="rounded border-2 border border-primary" width="40" id="blah3"></a>';
        });
        $dataTable = $dataTable->editColumn('name',  function ($row) {
            return $row->user_name;
        });
        $dataTable = $dataTable->editColumn('email',  function ($row) {
            return $row->user_email;
        })
        ->addColumn('role', function (Customer $customer) {
            return '<span class="badge bg-primary p-2 px-3">
                <span class="text-white">
                    ' . __('Customer') . '
                </span>
            </span>';
        });
        // `customer manage` is included because the action column now carries a
        // View button as well as edit and delete. Gating it on edit/delete alone
        // would hide the profile from anyone with read-only access — which is
        // most of the front desk.
        if (
            \Laratrust::hasPermission('customer manage') ||
            \Laratrust::hasPermission('customer edit') ||
            \Laratrust::hasPermission('customer delete')
        ) {
            $dataTable->addColumn('action', function (Customer $customer) {

                return view('customer.action', compact('customer'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Customer $model): QueryBuilder
    {
        $customer = $model
            ->select([
                'customers.*', 'users.name as user_name', 'users.email as user_email',
                \DB::raw('ROW_NUMBER() OVER (ORDER BY customers.id) AS DT_RowIndex')
            ])
            ->join('users', 'users.id', 'customers.user_id')
            ->where('customers.created_by', creatorId())
            ->where('customers.business_id', getActiveBusiness());

        return $customer;
    }
    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('customers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
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
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('avatar')->title(__('Avatar'))->searchable(false)->orderable(false)->exportable(false),
            Column::make('name')->title(__('Name'))->name('users.name'),
            Column::make('email')->title(__('Email'))->name('users.email'),
        ];
        $column[] = Column::computed('role');
        // Must match the gate in dataTable() exactly. If the column is declared
        // here but not populated there (or the reverse), DataTables errors with
        // "Requested unknown parameter 'action'" and the whole table fails to
        // render rather than just losing a button.
        if (
            \Laratrust::hasPermission('customer manage') ||
            \Laratrust::hasPermission('customer edit') ||
            \Laratrust::hasPermission('customer delete')

        ){
            $action = [
                Column::computed('action')->title(__('Action'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(100)
            ];
            $column = array_merge($column,$action);
        }

        return $column;
    
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'customers_' . date('YmdHis');
    }
}
