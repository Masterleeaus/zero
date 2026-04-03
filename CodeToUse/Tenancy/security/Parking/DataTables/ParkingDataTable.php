<?php

namespace Modules\Parking\DataTables;

use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\Parking\Entities\Parking;


class ParkingDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission = user()->permission('edit_parking');
        $this->deleteUnitPermission = user()->permission('delete_parking');
        $this->viewUnitPermission = user()->permission('view_parking');
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
            ->editColumn('name', function ($row) {
                return (ucwords($row->name));
            })
            ->editColumn('status', function ($row) {
                return ($row->status) ? ucwords(str_replace('-', ' ', $row->status))  : '--';
            })
            ->editColumn('request', function ($row) {
                return ($row->request) ? ucwords(str_replace('-', ' ', $row->request))  : '--';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('parking.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    $action .=
                    '<a class="dropdown-item" href="' . route('parking.edit', $row->id) . '" >
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

                $action .= '<a class="dropdown-item" href="' . route('parking.download', [$row->id]) . '">
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
    public function query(Parking $model)
    {
        $request = $this->request();
        $units = $model->select('*');

        if (!is_null($request->status) && $request->status != 'all')
        {
            $units->where('status', $request->status);
        } elseif (!is_null($request->req) && $request->req != 'all')
        {
            $units->where('req', $request->req);
        }
        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('status', 'like', '%' . request('searchText') . '%')
                    ->orWhere('no_hp', 'like', '%' . request('searchText') . '%')
                    ->orWhere('request', 'like', '%' . request('searchText') . '%')
                    ->orWhere('company_name', 'like', '%' . request('searchText') . '%');
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
        return $this->setBuilder('Parking-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["Parking-table"].buttons().container()
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
            '#' => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('parking::app.menu.resident') => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => __('parking::app.menu.resident')],
            __('parking::app.menu.status') => ['data' => 'status', 'name' => 'status', 'exportable' => true, 'title' => __('parking::app.menu.status')],
            __('parking::app.menu.noHP') => ['data' => 'no_hp', 'name' => 'no_hp', 'exportable' => true, 'title' => __('parking::app.menu.noHP')],
            __('parking::app.menu.reqType') => ['data' => 'request', 'name' => 'request', 'exportable' => true, 'title' => __('parking::app.menu.reqType')],
            __('parking::app.menu.companyName') => ['data' => 'company_name', 'name' => 'company_name', 'exportable' => true, 'title' => __('parking::app.menu.companyName')],
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
