<?php

namespace Modules\TrPackage\DataTables;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\TrPackage\Entities\Package;
use Modules\TrPackage\Entities\PackageItems;

class PickupDataTable extends BaseDataTable
{
    private $editPackagePermission;
    private $deletePackagePermission;
    private $viewPackagePermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editPackagePermission = user()->permission('edit_package');
        $this->deletePackagePermission = user()->permission('delete_package');
        $this->viewPackagePermission = user()->permission('view_package');
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
            ->editColumn('tanggal_diterima', function ($row) {
                return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '">'. Ucfirst($row->paket->tanggal_diterima) . '</a>';
            })
            ->editColumn('ekspedisi_id', function ($row) {
                $text = '';
                if ($row->paket->ekspedisi_id) {
                    $text = $row->paket->ekspedisi->name;
                }
                else {
                    $text = '--';
                }
                return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '">'. $text . '</a>';
            })
            ->editColumn('type_id', function ($row) {
                return ($row->type_id)  ? $row->type->name : '--';
            })
            ->editColumn('nama_penerima', function ($row) {
                return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '">'. Ucfirst($row->nama_penerima) . '</a>';
            })
            ->editColumn('unit_id', function ($row) {
                return ($row->unit_id)  ? $row->unit->unit_name : '--';
            })
            ->editColumn('status_ambil', function ($row) {
                if ($row->status_ambil == 'new') {
                    return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '"><i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status_ambil). '</a>';
                }
                else {
                    return '<a class="text-darkest-grey" href="' . route('pickup.edit', $row->id) . '"><i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.' . $row->status_ambil). '</a>';
                }
            })
            ->editColumn('nama_pengambil', function ($row) {
                return ($row->nama_pengambil)  ?? '--';
            })
            ->editColumn('no_hp_pengambil', function ($row) {
                return ($row->no_hp_pengambil)  ?? '--';
            })
            ->addColumn('action', function ($row) {

                $action = '
                <div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('pickup.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editPackagePermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('pickup.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
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
            ->rawColumns(['check', 'tanggal_diterima', 'action','ekspedisi_id','status_ambil','nama_penerima']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Package $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PackageItems $model)
    {
        $request = $this->request();

        // $model = $model->select('*');
        $model = $model->with('paket')
        ->select('tr_package_items.*')
        ->join('tr_package', 'tr_package.id', '=', 'tr_package_items.package_id');
        //$model->join('tr_package', 'tr_package.id', '=', 'tr_package_items.package_id');
        // $model = $model->with(['paket'])->select('*');
        // $model->join('tenan_package', 'tenan_package.id', '=', 'tenan_package_items.Package_id');

        if (!is_null($request->status) && $request->status != 'all') {
            $model->where('tr_package_items.status_ambil', $request->status);
        }

        if (!is_null($request->startDate) && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $model->where(DB::raw('tr_package.tanggal_diterima'), '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
            $model->where(DB::raw('tr_package.tanggal_diterima'), '<=', $endDate);
        }

        if ($request->searchText != '') {
            $model = $model->where(function ($query) {
                $query->where('tr_package.tanggal_diterima', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tr_package_items.nama_penerima', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tr_package_items.no_hp_pengambil', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tr_package_items.nama_pengambil', 'like', '%' . request('searchText') . '%')
                    //->orWhere('tr_package_ekspedisi.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->whereHas('type', function ($q) {
                            $q->where('name', 'like', '%' . request('searchText') . '%');
                        });
                    })
                    // ->orWhere(function ($query) {
                    //     $query->whereHas('ekspedisi', function ($q) {
                    //         $q->where('name', 'like', '%' . request('searchText') . '%');
                    //     });
                    // })
                    ->orWhere(function ($query) {
                        $query->whereHas('unit', function ($q) {
                            $q->where('unit_name', 'like', '%' . request('searchText') . '%');
                        });
                    });
            });
        }

        return $model;
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
            __('trpackage::app.menu.unit') => ['data' => 'unit_id', 'name' => 'unit_id', 'exportable' => true, 'title' => __('trpackage::app.menu.unit')],
            __('trpackage::app.menu.namaEkspedisi') => ['data' => 'ekspedisi_id', 'name' => 'ekspedisi_id', 'exportable' => true, 'title' => __('trpackage::app.menu.namaEkspedisi')],
            __('app.type') => ['data' => 'type_id', 'name' => 'type_id', 'exportable' => true, 'title' => __('app.type')],
            __('trpackage::app.menu.namaPenerima') => ['data' => 'nama_penerima', 'name' => 'nama_penerima', 'exportable' => true, 'title' => __('trpackage::app.menu.namaPenerima')],
            __('trpackage::app.menu.namaPengambil') => ['data' => 'nama_pengambil', 'name' => 'nama_pengambil', 'exportable' => true, 'title' => __('trpackage::app.menu.namaPengambil')],
            __('trpackage::app.menu.hpPengambil') => ['data' => 'no_hp_pengambil', 'name' => 'no_hp_pengambil', 'exportable' => true, 'title' => __('trpackage::app.menu.hpPengambil')],
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
