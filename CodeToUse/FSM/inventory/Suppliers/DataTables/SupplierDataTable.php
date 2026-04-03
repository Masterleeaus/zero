<?php

namespace Modules\Suppliers\DataTables;

use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Modules\Suppliers\Entities\Supplier;

class SupplierDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission = user()->permission('edit_suppliers');
        $this->deleteUnitPermission = user()->permission('delete_suppliers');
        $this->viewUnitPermission = user()->permission('view_suppliers');

    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('name', function ($row) {
                return (ucwords($row->name));
            })
            ->editColumn('alamat', function ($row) {
                return (ucwords($row->alamat));
            })
            ->editColumn('contact_person', function ($row) {
                return (ucwords($row->contact_person));
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('suppliers.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('suppliers.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }


                if ($this->deleteUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Supplier-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['check', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Supplier $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Supplier $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('supplier.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('supplier.alamat', 'like', '%' . request('searchText') . '%')
                    ->orWhere('supplier.phone', 'like', '%' . request('searchText') . '%');
            });
        }
        return $units;

    }

    public function child($child)
    {
        foreach ($child as $item) {
            $this->arr[] = $item->id;

            if ($item->childs) {
                $this->child($item->childs);
            }
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('Supplier-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["Supplier-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".statusChange").selectpicker();
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {

        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('suppliers::app.menu.name') => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => __('suppliers::app.menu.name')],
            __('suppliers::app.menu.alamat') => ['data' => 'alamat', 'name' => 'alamat', 'exportable' => true, 'title' => __('suppliers::app.menu.alamat')],
            __('suppliers::app.menu.phone') => ['data' => 'phone', 'name' => 'phone', 'exportable' => true, 'title' => __('suppliers::app.menu.phone')],
            __('suppliers::app.menu.contactPerson') => ['data' => 'contact_person', 'name' => 'contact_person', 'exportable' => true, 'title' => __('suppliers::app.menu.contactPerson')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }


}
