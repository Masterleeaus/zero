<?php

namespace Modules\Security\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\TrWorkPermits\Entities\WorkPermits;

class SecurityWPDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission   = user()->permission('edit_security');
        $this->deleteUnitPermission = user()->permission('delete_security');
        $this->viewUnitPermission   = user()->permission('view_security');
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
                return ($row->unit_id)  ? ucwords($row->unit->unit_name) : '--';
            })
            ->editColumn('date', function ($row) {
                return date('Y-m-d', strtotime($row->date));
            })
            ->editColumn('jenis_pekerjaan', function ($row) {
                return (ucwords($row->jenis_pekerjaan));
            })
            ->editColumn('lingkup_pekerjaan', function ($row) {
                return ($row->lingkup_pekerjaan)  ? ucwords(str_replace('-', ' ', $row->lingkup_pekerjaan)) : '--';
            })
            ->editColumn('approved_by', function ($row) {
                $status = '';

                if ($row->approved_by) {
                    $status .= ' <i class="fa fa-circle mr-1 text-dark-green f-10"></i>Approved';
                    $status .= '<br><span class="badge badge-secondary">by ' . $row->approved->name . '</span>';
                } else {
                    $status .= ' <i class="fa fa-circle mr-1 text-red f-10"></i>Not Yet Approved';
                }

                return $status;
            })
            ->editColumn('approved_bm', function ($row) {
                $status = '';

                if ($row->approved_bm) {
                    $status .= ' <i class="fa fa-circle mr-1 text-dark-green f-10"></i>Approved';
                    $status .= '<br><span class="badge badge-secondary">by ' . $row->approvedBm->name . '</span>';
                } else {
                    $status .= ' <i class="fa fa-circle mr-1 text-red f-10"></i>Not Yet Approved';
                }

                return $status;
            })
            ->editColumn('validated_by', function ($row) {
                $status = '';

                if ($row->validated_by) {
                    $status .= ' <i class="fa fa-circle mr-1 text-dark-green f-10"></i>Validated';
                    $status .= '<br><span class="badge badge-secondary">by ' . $row->validated->name . '</span>';
                } else {
                    $status .= ' <i class="fa fa-circle mr-1 text-red f-10"></i>Not Yet Validated';
                }

                return $status;
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                    <div class = "dropdown">
                    <a   class = "task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type = "link"
                         id    = "dropdownMenuLink-' . $row->id . '" data-toggle                                        = "dropdown" aria-haspopup = "true" aria-expanded = "false">
                    <i   class = "icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('security-workpermit.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    if ($row->approved_by != '' && $row->validated_by == '') {
                        $action .=
                            '<a class="dropdown-item" href="' . route('security-workpermit.validate', [$row->id]) . '">
                                <i class="fas fa-user-check mr-2"></i>
                                ' . trans('app.validate') . '
                            </a>';
                    }
                }

                $action .= '<a class="dropdown-item" href="' . route('security-workpermit.download', [$row->id]) . '">
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
            ->rawColumns(['action', 'approved_by', 'approved_bm', 'validated_by']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(WorkPermits $model)
    {
        $request = $this->request();

        $units = $model->select('*');
        $units->where('approved_at', '!=', '');

        if (!is_null($request->status_approve) && $request->status_approve != 'all') {
            if($request->status_approve == 'approve'){
                $units->where('approved_at', '!=', '');
            } elseif($request->status_approve == 'notApprove') {
                $units->whereNull('approved_at');
            }
        }

        if (!is_null($request->status_validate) && $request->status_validate != 'all') {
            if($request->status_validate == 'approve'){
                $units->where('validated_at', '!=', '');
            } elseif($request->status_validate == 'notApprove') {
                $units->whereNull('validated_at');
            }
        }

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $units->where(DB::raw('workpermits.date'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
            $units->where(DB::raw('workpermits.date'), '<=', $endDate);
        }

        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('date', 'like', '%' . request('searchText') . '%')
                    ->orWhere('name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('pj', 'like', '%' . request('searchText') . '%')
                    ->orWhere('no_hp', 'like', '%' . request('searchText') . '%')
                    ->orWhere('keterangan', 'like', '%' . request('searchText') . '%')
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
            '#'                                      => ['data' => 'id', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('security::app.menu.date')             => ['data' => 'date', 'name' => 'date', 'exportable' => true, 'title' => __('security::app.menu.date')],
            __('security::app.menu.unit')             => ['data' => 'unit_id', 'name' => 'unit_id', 'exportable' => true, 'title' => __('security::app.menu.unit')],
            __('security::app.menu.companyName')      => ['data' => 'company_name', 'name' => 'company_name', 'exportable' => true, 'title' => __('security::app.menu.companyName')],
            __('security::app.menu.workType')   => ['data' => 'jenis_pekerjaan', 'name' => 'jenis_pekerjaan', 'exportable' => true, 'title' => __('security::app.menu.workType')],
            __('security::app.menu.scopeOfWork') => ['data' => 'lingkup_pekerjaan', 'name' => 'lingkup_pekerjaan', 'exportable' => true, 'title' => __('security::app.menu.scopeOfWork')],
            __('security::app.menu.statusAproval')    => ['data' => 'approved_by', 'name' => 'approved_by', 'exportable' => true, 'title' => __('security::app.menu.statusAproval')],
            __('security::app.menu.statusAprovalBm') => ['data' => 'approved_bm', 'name' => 'approved_bm', 'exportable' => true, 'title' => __('security::app.menu.statusAprovalBm')],
            __('security::app.menu.statusValidasi')   => ['data' => 'validated_by', 'name' => 'validated_by', 'exportable' => true, 'title' => __('security::app.menu.statusValidasi')],
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
