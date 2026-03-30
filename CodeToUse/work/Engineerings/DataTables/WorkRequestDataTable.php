<?php

namespace Modules\Engineerings\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\Engineerings\Entities\WorkRequest;

class WorkRequestDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission   = user()->permission('edit_eng');
        $this->deleteUnitPermission = user()->permission('delete_eng');
        $this->viewUnitPermission   = user()->permission('view_eng');
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
            ->editColumn('wr_no', function ($row) {
                return ('#' . $row->wr_no);
            })
            ->editColumn('check_time', function ($row) {
                return ($row->check_time)  ? Carbon::createFromFormat('Y-m-d H:i:s', $row->check_time)->format('Y-m-d | H:i') : '--';
            })
            ->editColumn('complaint_id', function ($row) {
                return (ucwords($row->complaint_id))  ? ucwords($row->ticket->subject) : '--';
            })
            ->editColumn('remark', function ($row) {
                return (ucwords($row->remark)) ? ucwords($row->remark) : '--';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                <div class = "dropdown">
                <a   class = "task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type = "link"
                     id    = "dropdownMenuLink-' . $row->id . '" data-toggle                                        = "dropdown" aria-haspopup = "true" aria-expanded = "false">
                <i   class = "icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('engineerings.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    if ($row->status_wo != 1) {
                        $action .=
                            '<a class="dropdown-item" href="' . route('engineerings.edit', $row->id) . '" >
                                <i class = "fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                    }
                }

                if ($this->deleteUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-Unit-id="' . $row->id . '">
                                <i class = "fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('engineerings.download', [$row->id]) . '">
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
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(WorkRequest $model)
    {
        $request = $this->request();

        $units = $model->select('*');

        if (!is_null($request->type) && $request->type != 'all') {
            $units->where('typejournal_id', $request->type);
        }
        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('wr_no', 'like', '%' . request('searchText') . '%')
                    ->orWhere('check_time', 'like', '%' . request('searchText') . '%')
                    ->orWhere('remark', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->whereHas('ticket', function ($q) {
                            $q->where('subject', 'like', '%' . request('searchText') . '%');
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
            '#'                                    => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('engineerings::app.menu.noWR')      => ['data' => 'wr_no', 'name' => 'wr_no', 'exportable' => true, 'title' => __('engineerings::app.menu.noWR')],
            __('engineerings::app.menu.checkTime') => ['data' => 'check_time', 'name' => 'check_time', 'exportable' => true, 'title' => __('engineerings::app.menu.checkTime')],
            __('engineerings::app.menu.ticketID')  => ['data' => 'complaint_id', 'name' => 'complaint_id', 'exportable' => true, 'title' => __('engineerings::app.menu.ticketID')],
            __('engineerings::app.menu.remark')    => ['data' => 'remark', 'name' => 'remark', 'exportable' => true, 'title' => __('engineerings::app.menu.remark')],
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
