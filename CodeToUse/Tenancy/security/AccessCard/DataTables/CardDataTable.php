<?php

namespace Modules\TrAccessCard\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\TrAccessCard\Entities\TrAccessCard;

class CardDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission = user()->permission('edit_access_card');
        $this->deleteUnitPermission = user()->permission('delete_access_card');
        $this->viewUnitPermission = user()->permission('view_access_card');
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
            ->editColumn('date', function ($row) {
                return ($row->date);
            })
            ->editColumn('unit_id', function ($row) {
                return ($row->unit_id)  ? $row->unit->unit_name : '--';
            })
            ->editColumn('name', function ($row) {
                return ($row->name);
            })
            ->editColumn('no_hp', function ($row) {
                return ($row->no_hp);
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('card-access.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item" href="' . route('card-access.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.approve') . '
                            </a>';
                }


                if ($this->deleteUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Unit-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('card-access.download', [$row->id]) . '">
                <i class="fa fa-download mr-2"></i>
                ' . trans('app.download') . '
                </a>';

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
    public function query(TrAccessCard $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $units->where(DB::raw('tr_access_card.date'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
            $units->where(DB::raw('tr_access_card.date'), '<=', $endDate);
        }

        if ($this->viewUnitPermission == 'owned') {
            $units->where(function ($query) {
                $query->where('tr_access_card.created_by', '=', user()->id);
            });
        }

        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('date', 'like', '%' . request('searchText') . '%')
                    ->orWhere('name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('fee', 'like', '%' . request('searchText') . '%')
                    ->orWhere('no_hp', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->whereHas('unit', function ($q) {
                            $q->where('unit_name', 'like', '%' . request('searchText') . '%');
                        });
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

        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('traccesscard::app.menu.reqDate') => ['data' => 'date', 'name' => 'date', 'exportable' => true, 'title' => __('traccesscard::app.menu.reqDate')],
            __('traccesscard::app.menu.unit') => ['data' => 'unit_id', 'name' => 'unit_id', 'exportable' => true, 'title' => __('traccesscard::app.menu.unit')],
            __('traccesscard::app.menu.resident') => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => __('traccesscard::app.menu.resident')],
            __('traccesscard::app.menu.noHP') => ['data' => 'no_hp', 'name' => 'no_hp', 'exportable' => true, 'title' => __('traccesscard::app.menu.noHP')],
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
