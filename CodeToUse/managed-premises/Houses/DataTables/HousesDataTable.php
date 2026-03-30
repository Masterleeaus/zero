<?php

namespace Modules\Houses\DataTables;

use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Modules\Houses\Entities\House;

class HousesDataTable extends BaseDataTable
{
    private $editHousePermission;
    private $deleteHousePermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editHousePermission = user()->permission('edit_house');
        $this->deleteHousePermission = user()->permission('delete_house');
        $this->viewHousePermission = user()->permission('view_house');

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
            ->editColumn('house_code', function ($row) {
                return ($row->house_code);
            })
            ->editColumn('house_name', function ($row) {
                return ($row->house_name);
            })
            ->editColumn('area_id', function ($row) {
                return ($row->area_id)  ? $row->area->area_name : '--';
            })
            ->editColumn('tower_id', function ($row) {
                return ($row->tower_id)  ? $row->tower->tower_name : '--';
            })
            ->editColumn('typehouse_id', function ($row) {
                return ($row->typehouse_id)  ? $row->typehouse->typehouse_name : '--';
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

                $action .= '<a href="' . route('houses.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editHousePermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('houses.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }


                if ($this->deleteHousePermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-House-id="' . $row->id . '">
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
     * @param \App\House $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(House $model)
    {
        $request = $this->request();

        $houses = $model->select('*');

        if (!is_null($request->area) && $request->area != 'all') {
            $houses->where('area_id', $request->area);
        }
        if (!is_null($request->tower) && $request->tower != 'all') {
            $houses->where('tower_id', $request->tower);
        }
        if (!is_null($request->typehouse) && $request->typehouse != 'all') {
            $houses->where('typehouse_id', $request->typehouse);
        }
        if ($request->searchText != '') {
            $houses = $houses->where(function ($query) {
                $query->where('houses.house_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('houses.house_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('houses.luas', 'like', '%' . request('searchText') . '%');
            });
        }
        return $houses;

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
        return $this->setBuilder('House-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["House-table"].buttons().container()
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
            __('houses::modules.house.houseCode') => ['data' => 'house_code', 'name' => 'house_code', 'exportable' => true, 'title' => __('houses::modules.house.houseCode')],
            __('houses::modules.house.houseArea') => ['data' => 'area_id', 'name' => 'area_id', 'exportable' => true, 'title' => __('houses::modules.house.houseArea')],
            __('houses::modules.house.houseTower') => ['data' => 'tower_id', 'name' => 'tower_id', 'exportable' => true, 'title' => __('houses::modules.house.houseTower')],
            __('houses::modules.house.houseTypeHouse') => ['data' => 'typehouse_id', 'name' => 'typehouse_id', 'exportable' => true, 'title' => __('houses::modules.house.houseTypeHouse')],
            __('houses::modules.house.houseLuas') => ['data' => 'luas', 'name' => 'luas', 'exportable' => true, 'title' => __('houses::modules.house.houseLuas')],
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
