<?php

namespace Modules\Units\DataTables;

use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Modules\Units\Entities\UsersUnit;

class UnitsConfigurationDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission   = user()->permission('edit_unit');
        $this->deleteUnitPermission = user()->permission('delete_unit');
        $this->viewUnitPermission   = user()->permission('view_unit');
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
            ->editColumn('user_id', function ($row) {
                return ($row->user_id) ? ucfirst($row->user->name) : '--';
            })
            ->addColumn('total', function ($row) {
                return ($row->total_units);
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <div class = "dropdown">
                    <a   class = "task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type = "link"
                         id    = "dropdownMenuLink-' . $row->user_id . '" data-toggle                                        = "dropdown" aria-haspopup = "true" aria-expanded = "false">
                    <i   class = "icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->user_id . '" tabindex="0">';

                $action .= '<a href="' . route('units-configuration.show', [$row->user_id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('units-configuration.edit', [$row->user_id]) . '">
                                <i class = "fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-unit-id="' . $row->user_id . '">
                                <i class = "fa fa-trash mr-2"></i>
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
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\UsersUnit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(UsersUnit $model)
    {
        $request = $this->request();

        $units = $model->select('user_id', DB::raw('count(DISTINCT unit_id) as total_units'))
        ->groupBy('user_id');

        if ($request->searchText != '') {
            $searchText = request('searchText');
            $units = $units->where(function ($query) use ($searchText) {
                $query->orWhereHas('user', function ($q) use ($searchText) {
                    $q->where('name', 'like', '%' . $searchText . '%');
                });
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
        return $this->setBuilder('UsersUnit-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["UsersUnit-table"].buttons().container()
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
            '#' => ['data' => 'user_id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('units::modules.unit.user') => ['data' => 'user_id', 'name' => 'user_id', 'exportable' => true, 'title' => __('units::modules.unit.user')],
            Column::computed('total', __('units::modules.unit.totalUnit'))
                ->exportable(true)
                ->printable(true)
                ->orderable(true)
                ->searchable(true)
                ->width(150)
                ->addClass('text-right pr-20'),
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
