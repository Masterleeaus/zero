<?php

namespace Modules\Engineerings\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\Engineerings\Entities\Meter;


class MeterDataTable extends BaseDataTable
{
    private $editPermissions;
    private $deletePermissions;
    private $viewPermissions;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editPermissions   = user()->permission('edit_eng');
        $this->deletePermissions = user()->permission('delete_eng');
        $this->viewPermissions   = user()->permission('view_eng');
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
            ->editColumn('unit_id', function ($row) {
                return ($row->unit_id)  ? $row->unit->unit_name : '--';
            })
            ->editColumn('billing_date', function ($row) {
                return (Carbon::createFromFormat('Y-m-d', $row->billing_date)->format('Y-m-d'));
            })
            ->editColumn('type_bill', function ($row) {
                return (strtoupper($row->type_bill));
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                <div class = "dropdown">
                <a   class = "task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type = "link"
                     id    = "dropdownMenuLink-' . $row->id . '" data-toggle                                        = "dropdown" aria-haspopup = "true" aria-expanded = "false">
                <i   class = "icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('meter.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editPermissions == 'all') {
                    if ($row->status_wo != 1) {
                        $action .=
                            '<a class="dropdown-item" href="' . route('meter.edit', $row->id) . '" >
                                <i class = "fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                    }
                }

                if ($this->deletePermissions == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Unit-id="' . $row->id . '">
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
            ->rawColumns(['check', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Meter $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if (!is_null($request->type) && $request->type != 'all') {
            $units->where('typejournal_id', $request->type);
        }
        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('end_meter', 'like', '%' . request('searchText') . '%')
                    ->orWhere('type_bill', 'like', '%' . request('searchText') . '%')
                    ->orWhere('billing_date', 'like', '%' . request('searchText') . '%')
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
        return $this->setBuilder('Journal-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["Journal-table"].buttons().container()
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
            '#'                                      => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('engineerings::app.menu.unitID')      => ['data' => 'unit_id', 'name' => 'unit_id', 'exportable' => true, 'title' => __('engineerings::app.menu.unitID')],
            __('engineerings::app.menu.endMeter')    => ['data' => 'end_meter', 'name' => 'end_meter', 'exportable' => true, 'title' => __('engineerings::app.menu.endMeter')],
            __('engineerings::app.menu.typeBill')    => ['data' => 'type_bill', 'name' => 'type_bill', 'exportable' => true, 'title' => __('engineerings::app.menu.typeBill')],
            __('engineerings::app.menu.billingDate') => ['data' => 'billing_date', 'name' => 'billing_date', 'exportable' => true, 'title' => __('engineerings::app.menu.billingDate')],
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
