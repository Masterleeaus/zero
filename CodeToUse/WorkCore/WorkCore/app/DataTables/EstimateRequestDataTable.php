<?php

namespace App\DataTables;

use App\Models\EstimateRequest;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Str;
use App\Helper\UserService;
use App\Models\ClientContact;
use App\Helper\Common;

class EstimateRequestDataTable extends BaseDataTable
{
    private $addEstimatePermission;
    private $editEstimateRequestPermission;
    private $deleteEstimateRequestPermission;
    private $rejectEstimateRequestPermission;
    private $viewEstimateRequestPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addEstimatePermission = user()->permission('add_estimates');
        $this->editEstimateRequestPermission = user()->permission('edit_estimate_request');
        $this->deleteEstimateRequestPermission = user()->permission('delete_estimate_request');
        $this->rejectEstimateRequestPermission = user()->permission('reject_estimate_request');
        $this->viewEstimateRequestPermission = user()->permission('view_estimate_request');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $userId = UserService::getUserId();
        $clientIds = ClientContact::where('user_id', $userId)->pluck('client_id')->toArray();
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('estimate_request_number', function ($row) {
                return '<a href="' . route('quote-request.show', $row->id) . '" class="text-darkest-grey openRightModal">' . $row->estimate_request_number . '</a>';
            })
            ->editColumn('customer', function ($row) {
                return '<div class="media align-items-center">
                    <a href="' . route('customers.show', [$row->client_id]) . '">
                    <img src="' . $row->customer->image_url . '" class="mr-2 taskEmployeeImg rounded-circle" alt="' . $row->customer->name . '" title="' . $row->customer->name . '"></a>
                    <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('customers.show', [$row->client_id]) . '">' . $row->customer->name_salutation . '</a></h5>
                    <p class="mb-0 f-13 text-dark-grey">' . $row->customer->clientDetails?->company_name . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('estimated_budget', function ($row) {
                return currency_format($row->estimated_budget, $row->currency_id);
            })
            ->editColumn('site', function ($row) {
                if ($row->project_id) {
                    return '<div class="media align-items-center">
                                <div class="media-body">
                            <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('sites.show', [$row->project_id]) . '">' . $row->site->project_name . '</a></h5>
                            </div>
                        </div>';
                }

                return '--';
            })
            ->editColumn('estimate_id', function ($row) {
                if ($row->quote) {
                    return '<a class="text-darkest-grey" href="' . route('quotes.show', [$row->quote->id]) . '">' . $row->quote->estimate_number . '</a>';
                } else {
                    return '--';
                }
            })
            ->addColumn('action', function ($row) use ($userId, $clientIds) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('quote-request.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($row->status == 'pending') {
                    if (
                        $this->editEstimateRequestPermission == 'all'
                        || (($this->editEstimateRequestPermission == 'added') && ($row->added_by == user()->id || $row->added_by == $userId || in_array($row->added_by, $clientIds)))
                        || (($this->editEstimateRequestPermission == 'owned') && $row->client_id == $userId)
                        || (($this->editEstimateRequestPermission == 'both') && ($row->added_by == user()->id || $row->client_id == $userId || $row->added_by == $userId || in_array($row->added_by, $clientIds)))
                    ) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('quote-request.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>' . trans('app.edit') . ' </a>';
                    }

                    if ($this->rejectEstimateRequestPermission == 'all') {
                        $action .= '<a class="dropdown-item change-status" href="javascript:;" data-quote-request-id="' . $row->id . '">
                            <i class="fa fa-times mr-2"></i>' . trans('app.reject') . ' </a>';
                    }
                }

                if ($row->status != 'accepted') {
                    if ($this->addEstimatePermission == 'all' || $this->addEstimatePermission == 'added') {
                        $action .= '<a class="dropdown-item" href="' . route('quotes.create') . '?quote-request=' . $row->id . '">
                            <i class="fa fa-plus mr-2"></i>
                            ' . trans('app.create') . ' ' . trans('app.menu.quote') . '
                        </a>';
                    }
                }

                if (
                    $this->deleteEstimateRequestPermission == 'all'
                    || (($this->deleteEstimateRequestPermission == 'added') && ($row->added_by == user()->id || $row->added_by == $userId || in_array($row->added_by, $clientIds)))
                    || (($this->deleteEstimateRequestPermission == 'owned') && $row->client_id == $userId)
                    || (($this->deleteEstimateRequestPermission == 'both') && ($row->added_by == user()->id || $row->client_id == $userId || $row->added_by == $userId || in_array($row->added_by, $clientIds)))
                ) {
                    if (!(in_array('customer', user_roles()) && $row->status == 'accepted')) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-quote-request-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
                    }
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('status1', function ($row) {

                $select = '';

                if ($row->status == 'pending' || $row->status == 'in process') {
                    $select .= '<i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('app.pending') . '</label>';
                } elseif ($row->status == 'rejected') {
                    $select .= '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status) . '</label>';
                } else {
                    $select .= '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.' . $row->status) . '</label>';
                }

                return $select;
            })
            ->addColumn('status_name', function ($row) {
                return $row->status;
            })
            ->addColumn('early_requirement', function ($row) {
                return $row->early_requirement ?? '--';
            })
            ->rawColumns(['action', 'status1', 'customer', 'site', 'estimate_id', 'estimate_request_number']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EstimateRequest $model)
    {
        $userId = UserService::getUserId();
        $searchText = request('searchText');
        $model = $model->select('estimate_requests.*')
            ->leftJoin('quotes', 'quotes.id', '=', 'estimate_requests.estimate_id')
            ->leftJoin('users', 'users.id', '=', 'estimate_requests.client_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'estimate_requests.currency_id')
            ->leftJoin('sites', 'sites.id', '=', 'estimate_requests.project_id')
            ->leftJoin('client_details', 'client_details.user_id', '=', 'users.id')
            ->withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where(function ($query) use ($searchText) {
                $safeTerm = Common::safeString(request('searchText'));
                $query->where('users.name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('users.email', 'like', '%' . $safeTerm . '%');
            });

        if (request()->has('customer') && request()->customer != 'all') {
            $model = $model->whereHas('customer', function ($query) {
                $query->where('id', request()->customer);
            });
        }

        if (in_array('customer', user_roles())) {
            $model = $model->where('estimate_requests.client_id', $userId);
        }

        if (request()->has('status') && request()->status != 'all') {
            if (request()->status == 'pending') {
                $model = $model->whereIn('estimate_requests.status', ['pending', 'in process']);
            } else {
                $model = $model->where('estimate_requests.status', request()->status);
            }
        }

        if ($this->viewEstimateRequestPermission == 'added') {
            $model->where('estimate_requests.added_by', $userId);
        }

        if ($this->viewEstimateRequestPermission == 'both') {
            $model->where(function ($query) use ($userId) {
                $query->where('estimate_requests.added_by', $userId)
                    ->orWhere('estimate_requests.client_id', $userId);
            });
        }

        if ($this->viewEstimateRequestPermission == 'owned') {
            $model->where('estimate_requests.client_id', $userId);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('quote-request-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["quote-request-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number') => ['data' => 'estimate_request_number', 'name' => 'estimate_request_number', 'title' => __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number')],
            __('app.clientName') => ['data' => 'customer', 'name' => 'users.name', 'title' => __('app.clientName')],
            __('app.site') => ['data' => 'site', 'name' => 'sites.project_name', 'title' => __('app.site')],
            __('modules.estimateRequest.estimatedBudget') => ['data' => 'estimated_budget', 'name' => 'estimated_budget', 'title' => __('modules.estimateRequest.estimatedBudget')],
            __('app.quote') => ['data' => 'estimate_id', 'name' => 'estimate_id', 'title' => __('app.quote')],
            __('app.status') => ['data' => 'status1', 'name' => 'status', 'width' => '10%', 'exportable' => false, 'visible' => true, 'title' => __('app.status')],
            __('modules.estimateRequest.earlyRequirement') => ['data' => 'early_requirement', 'name' => 'early_requirement',  'visible' => false],
            __('modules.estimateRequest.estimateRequest') . ' ' . __('app.status') => ['data' => 'status_name', 'name' => 'status', 'visible' => false, 'exportable' => true, 'title' => __('modules.estimateRequest.estimateRequest') . ' ' . __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(100)
                ->addClass('text-right pr-20')
        ];
    }
}
