<?php

namespace Modules\Units\DataTables;

use Modules\Units\Entities\Unit;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\Units\Entities\UsersUnit;
use Yajra\DataTables\EloquentDataTable;

class UnitsDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission = user()->permission('edit_unit');
        $this->deleteUnitPermission = user()->permission('delete_unit');
        $this->viewPermission = user()->permission('view_unit');
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
            ->editColumn('unit_code', function ($row) {
                return ($row->unit_code);
            })
            ->editColumn('unit_name', function ($row) {
                return ($row->unit_name);
            })
            ->editColumn('floor_id', function ($row) {
                return ($row->floor_id)  ? $row->floor->floor_name : '--';
            })
            ->editColumn('tower_id', function ($row) {
                return ($row->tower_id)  ? $row->tower->tower_name : '--';
            })
            ->editColumn('typeunit_id', function ($row) {
                return ($row->typeunit_id)  ? $row->typeunit->typeunit_name : '--';
            })
            ->editColumn('luas', function ($row) {
                return ($row->luas);
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('units.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                $hasAccess = UsersUnit::where('user_id', user()->id)
                ->where('unit_id', $row->id)
                ->exists();
                
                if ($this->editUnitPermission == 'all' || $hasAccess) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('units.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }


                if ($this->deleteUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Unit-id="' . $row->id . '">
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
     * @param \App\Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Unit $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if ($this->viewPermission == 'none') {
            $units->whereIn('units.id', function ($query) {
                $query->select('unit_id')
                    ->from('users_units')
                    ->where('user_id', user()->id);
            });
        }

        if (!is_null($request->floor) && $request->floor != 'all') {
            $units->where('floor_id', $request->floor);
        }
        if (!is_null($request->tower) && $request->tower != 'all') {
            $units->where('tower_id', $request->tower);
        }
        if (!is_null($request->typeunit) && $request->typeunit != 'all') {
            $units->where('typeunit_id', $request->typeunit);
        }
        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('units.unit_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('units.unit_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('units.luas', 'like', '%' . request('searchText') . '%');
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
        return $this->setBuilder('Unit-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["Unit-table"].buttons().container()
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
        $columns = [];

        if ($this->viewPermission == 'all') {
            $columns['check'] = [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ];
        }
    
        $columns += [
            '#' => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('units::modules.unit.unitCode') => ['data' => 'unit_code', 'name' => 'unit_code', 'exportable' => true, 'title' => __('units::modules.unit.unitCode')],
            __('units::modules.unit.unitName') => ['data' => 'unit_name', 'name' => 'unit_name', 'exportable' => true, 'title' => __('units::modules.unit.unitName')],
            __('units::modules.unit.unitFloor') => ['data' => 'floor_id', 'name' => 'floor_id', 'exportable' => true, 'title' => __('units::modules.unit.unitFloor')],
            __('units::modules.unit.unitTower') => ['data' => 'tower_id', 'name' => 'tower_id', 'exportable' => true, 'title' => __('units::modules.unit.unitTower')],
            __('units::modules.unit.unitTypeUnit') => ['data' => 'typeunit_id', 'name' => 'typeunit_id', 'exportable' => true, 'title' => __('units::modules.unit.unitTypeUnit')],
            __('units::modules.unit.unitLuas') => ['data' => 'luas', 'name' => 'luas', 'exportable' => true, 'title' => __('units::modules.unit.unitLuas')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    
        return $columns;
    }
}
