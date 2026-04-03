<?php

namespace Modules\TrPackage\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\TrPackage\Entities\Package;

class ReceiveDataTable extends BaseDataTable
{
    private $editUnitPermission;
    private $deleteUnitPermission;
    private $viewUnitPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editUnitPermission = user()->permission('edit_package');
        $this->deleteUnitPermission = user()->permission('delete_package');
        $this->viewUnitPermission = user()->permission('view_package');

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
            ->editColumn('ekspedisi_id', function ($row) {
                return ($row->ekspedisi_id)  ? $row->ekspedisi->name : '--';
            })

            ->editColumn('nama_pengirim', function ($row) {
                return Ucfirst($row->nama_pengirim);
            })
            ->editColumn('status_ambil', function ($row) {
                if ($row->status_ambil == 'new') {
                    return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '"><i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status_ambil). '</a>';
                }
                else {
                    return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '"><i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.' . $row->status_ambil). '</a>';
                }
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
                $action .= '<a href="' . route('receive.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editUnitPermission == 'all') {
                    $action .= '<a class="dropdown-item" href="' . route('receive.edit', [$row->id]) . '">
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
            ->rawColumns(['check', 'action','status_ambil']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Package $model)
    {
        $request = $this->request();

        $units = $model->select('*');
        if (!is_null($request->status) && $request->status != 'all') {
            $units->where('status_ambil', $request->status);
        }

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $units->where(DB::raw('tr_package.tanggal_diterima'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
            $units->where(DB::raw('tr_package.tanggal_diterima'), '<=', $endDate);
        }

        if ($request->searchText != '') {
            $units = $units->where(function ($query) {
                $query->where('tanggal_diterima', 'like', '%' . request('searchText') . '%')
                    ->orWhere('nama_pengirim', 'like', '%' . request('searchText') . '%')
                    ->orWhere('no_hp_pengirim', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->whereHas('ekspedisi', function ($q) {
                            $q->where('name', 'like', '%' . request('searchText') . '%');
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
            __('trpackage::app.menu.date') => ['data' => 'tanggal_diterima', 'name' => 'tanggal_diterima', 'exportable' => true, 'title' => __('trpackage::app.menu.date')],
            __('trpackage::app.menu.namaEkspedisi') => ['data' => 'ekspedisi_id', 'name' => 'ekspedisi_id', 'exportable' => true, 'title' => __('trpackage::app.menu.namaEkspedisi')],
            __('trpackage::app.menu.namaPengirim') => ['data' => 'nama_pengirim', 'name' => 'nama_pengirim', 'exportable' => true, 'title' => __('trpackage::app.menu.namaPengirim')],
            __('trpackage::app.menu.hpPengirim') => ['data' => 'no_hp_pengirim', 'name' => 'no_hp_pengirim', 'exportable' => true, 'title' => __('trpackage::app.menu.hpPengirim')],
            __('trpackage::app.menu.jamAmbil') => ['data' => 'jam', 'name' => 'jam', 'exportable' => true, 'title' => __('trpackage::app.menu.jamAmbil')],
            __('trpackage::app.menu.status') => ['data' => 'status_ambil', 'name' => 'status_ambil', 'exportable' => true, 'title' => __('trpackage::app.menu.status')],
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
