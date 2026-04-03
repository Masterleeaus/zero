<?php

namespace Modules\Engineerings\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\Engineerings\Entities\WorkOrder;


class WorkOrderDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $viewPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editPermission   = user()->permission('edit_eng');
        $this->deletePermission = user()->permission('delete_eng');
        $this->viewPermission   = user()->permission('view_eng');
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
            ->editColumn('nomor_wo', function ($row) {
                return ('#' . $row->nomor_wo);
            })
            ->editColumn('category', function ($row) {
                return $row->category ? ucwords($row->category) : '--';
            })
            ->editColumn('priority', function ($row) {
                return $row->priority ? ucwords($row->priority) : '--';
            })
            ->editColumn('schedule_start', function ($row) {
                return $row->schedule_start ? Carbon::createFromFormat('Y-m-d H:i:s', $row->schedule_start)->format('Y-m-d | H:i') : '--';
            })
            ->editColumn('status', function ($row) {
                return $row->status ? ucwords($row->status) : '--';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                <div class = "dropdown">
                <a   class = "task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type = "link"
                     id    = "dropdownMenuLink-' . $row->id . '" data-toggle                                        = "dropdown" aria-haspopup = "true" aria-expanded = "false">
                <i   class = "icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('work.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editPermission == 'all') {
                    if ($row->status_wo != 1) {
                        $action .=
                            '<a class="dropdown-item" href="' . route('work.edit', $row->id) . '" >
                                <i class = "fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                    }
                }

                if ($this->deletePermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Unit-id="' . $row->id . '">
                                <i class = "fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('work.download', [$row->id]) . '">
                <i class = "fa fa-download mr-2"></i>
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
    public function query(WorkOrder $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if (!is_null($request->type) && $request->type != 'all') {
            $units->where('typejournal_id', $request->type);
        }
        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('nomor_wo', 'like', '%' . request('searchText') . '%')
                    ->orWhere('category', 'like', '%' . request('searchText') . '%')
                    ->orWhere('priority', 'like', '%' . request('searchText') . '%')
                    ->orWhere('status', 'like', '%' . request('searchText') . '%');
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
            '#'                                        => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('engineerings::app.menu.noWO')          => ['data' => 'nomor_wo', 'name' => 'nomor_wo', 'exportable' => true, 'title' => __('engineerings::app.menu.noWO')],
            __('Category')                             => ['data' => 'category', 'name' => 'category', 'exportable' => true, 'title' => __('engineerings::app.menu.category')],
            __('engineerings::app.menu.priority')      => ['data' => 'priority', 'name' => 'priority', 'exportable' => true, 'title' => __('engineerings::app.menu.priority')],
            __('engineerings::app.menu.scheduleStart') => ['data' => 'schedule_start', 'name' => 'schedule_start', 'exportable' => true, 'title' => __('engineerings::app.menu.scheduleStart')],
            __('engineerings::app.menu.status')        => ['data' => 'status', 'name' => 'status', 'exportable' => true, 'title' => __('engineerings::app.menu.status')],
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
