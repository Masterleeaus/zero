<?php

namespace App\DataTables;

use App\Models\Service Agreement;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\GlobalSetting;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Helper\UserService;
use App\Helper\Common;

class ContractsDataTable extends BaseDataTable
{

    private $editContractPermission;
    private $deleteContractPermission;
    private $addContractPermission;
    private $viewContractPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editContractPermission = user()->permission('edit_contract');
        $this->deleteContractPermission = user()->permission('delete_contract');
        $this->addContractPermission = user()->permission('add_contract');
        $this->viewContractPermission = user()->permission('view_contract');
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
        $datatables = datatables()->eloquent($query);

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Service Agreement::CUSTOM_FIELD_MODEL);

        return $datatables
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) use ($userId) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= ' <a href="' . route('service agreements.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if (!in_array('customer', user_roles()) && !$row->company_sign && user()->company_id == $row->company_id) {
                    $action .= '<a class="dropdown-item sign-modal" href="javascript:;" data-service agreement-id="' . $row->id . '">
                    <i class="fa fa-check mr-2"></i>
                    ' . trans('modules.quotes.companysignature') . '
                    </a>';
                }

                if (!$row->signature) {
                    $action .= '<a class="dropdown-item" href="' . url()->temporarySignedRoute('front.service agreement.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), [$row->hash]) . '" target="_blank"><i class="fa fa-link mr-2"></i>' . __('modules.proposal.publicLink') . '</a>';
                }

                if (in_array('customers', user_modules()) && ($this->addContractPermission == 'all' || $this->addContractPermission == 'added')) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('service agreements.create') . '?id=' . $row->id . '">
                            <i class="fa fa-copy mr-2"></i>
                            ' . __('app.copy') . ' ' . __('app.menu.service agreement') . '
                        </a>';
                }

                if (
                    in_array('customers', user_modules()) &&
                    ($this->editContractPermission == 'all'
                        || ($this->editContractPermission == 'added' && $userId == $row->added_by)
                        || ($this->editContractPermission == 'owned' && $userId == $row->client_id)
                        || ($this->editContractPermission == 'both' && ($userId == $row->client_id || $userId == $row->added_by)))
                ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('service agreements.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
                }

                if (
                    $this->deleteContractPermission == 'all'
                    || ($this->deleteContractPermission == 'added' && $userId == $row->added_by)
                    || ($this->deleteContractPermission == 'owned' && $userId == $row->client_id)
                    || ($this->deleteContractPermission == 'both' && ($userId == $row->client_id || $userId == $row->added_by))
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-service agreement-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('service agreements.download', $row->id) . '">
                                <i class="fa fa-download mr-2"></i>
                                ' . trans('app.download') . '
                            </a>';


                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->editColumn('project_name', function ($row) {
                if ($row->project_id != null) {
                    return '<a href="' . route('sites.show', $row->project_id) . '" class="text-darkest-grey">' . str($row->site->project_name)->limit(30) . '</a>';
                }

                return '--';
            })
            ->addColumn('contract_subject', function ($row) {
                return str($row->subject)->limit(50);
            })
            ->editColumn('subject', function ($row) {
                $signed = '';

                if ($row->signature) {
                    $signed = '<span class="badge badge-secondary"><i class="fa fa-signature"></i> ' . __('app.signed') . '</span>';
                }

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('service agreements.show', [$row->id]) . '">' . str($row->subject)->limit(40) . '</a></h5>
                    <p class="mb-0">' . $signed . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date->translatedFormat($this->company->date_format);
            })
            ->editColumn('end_date', function ($row) {
                if (is_null($row->end_date)) {
                    return '--';
                }

                return $row->end_date == null ? $row->end_date : $row->end_date->translatedFormat($this->company->date_format);
            })
            ->editColumn('amount', function ($row) {
                return currency_format($row->amount, $row->currency->id);
            })
            ->addColumn('client_name', function ($row) {
                if ($row->customer) {
                    $customer = $row->customer;

                    return view('components.customer', [
                        'user' => $customer
                    ]);
                }

                return '--';
            })
            ->editColumn('customer.name', function ($row) {
                return '<div class="media align-items-center">
                    <a href="' . route('customers.show', [$row->client_id]) . '">
                    <img src="' . $row->customer->image_url . '" class="mr-2 taskEmployeeImg rounded-circle" alt="' . $row->customer->name . '" title="' . $row->customer->name . '"></a>
                    <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('customers.show', [$row->client_id]) . '">' . $row->customer->name_salutation . '</a></h5>
                    <p class="mb-0 f-13 text-dark-grey">' . $row->customer->clientDetails->company_name . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('signature', function ($row) {
                if ($row->signature) {
                    return __('app.signed');
                }
            })
            ->editColumn('contract_number', function ($row) {
                return '<a href="' . route('service agreements.show', [$row->id]) . '" class="text-darkest-grey">' . $row->contract_number . '</a>';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(array_merge(['project_name', 'action', 'customer.name', 'check', 'subject', 'contract_number'], $customFieldColumns));
    }

    /**
     * @param Service Agreement $model
     * @return \Illuminate\Database\Eloquent\Builder
     * @property-read \App\Models\Award $title
     */
    public function query(Service Agreement $model)
    {
        $request = $this->request();
        $startDate = null;
        $endDate = null;
        $userId = UserService::getUserId();

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $model = $model->with(
            [
                'company',
                'site' => function ($q) {
                    $q->withTrashed();
                    $q->select('id', 'project_name', 'project_short_code', 'client_id');
                },
                'customer.clientDetails.company:id,company_name',
                'currency:id,currency_symbol,currency_code',
                'site.customer',
                'site.customer.clientDetails.company',
                'customer',
                'site.clientdetails'
            ]
        )->with('contractType', 'customer', 'signature', 'customer.clientDetails')
            ->join('users', 'users.id', '=', 'service agreements.client_id')
            ->join('client_details', 'users.id', '=', 'client_details.user_id')
            ->select('service agreements.*');

        if ($startDate !== null && $endDate !== null) {
            $model->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(service agreements.`end_date`)'), [$startDate, $endDate]);

                $q->orWhereBetween(DB::raw('DATE(service agreements.`start_date`)'), [$startDate, $endDate]);
            });
        }

        if ($request->customer != 'all' && !is_null($request->customer)) {
            $model = $model->where('service agreements.client_id', '=', $request->customer);
        }

        if ($request->contract_type != 'all' && !is_null($request->contract_type)) {
            $model = $model->where('service agreements.contract_type_id', '=', $request->contract_type);
        }

        if (request('signed') == 'yes') {
            $model = $model->has('signature');
        }

        if ($request->searchText != '') {

            $model = $model->where(function ($query) {
                $safeTerm = Common::safeString(request('searchText'));
                $query->where('service agreements.subject', 'like', '%' . $safeTerm . '%')
                    ->orWhere('service agreements.amount', 'like', '%' . $safeTerm . '%')
                    ->orWhere('client_details.company_name', 'like', '%' . $safeTerm . '%');
            })
                ->orWhere(function ($query) {
                    $query->whereHas('site', function ($q) {
                        $safeTerm = Common::safeString(request('searchText'));
                        $q->where('project_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('project_short_code', 'like', '%' . $safeTerm . '%'); // site short code
                    });
                });
        }

        if ($this->viewContractPermission == 'added') {
            $model = $model->where('service agreements.added_by', '=', $userId);
        }

        if ($this->viewContractPermission == 'owned') {
            $model = $model->where('service agreements.client_id', '=', $userId);
        }

        if ($this->viewContractPermission == 'both') {
            $model = $model->where(function ($query) use ($userId) {
                $query->where('service agreements.added_by', '=', $userId)
                    ->orWhere('service agreements.client_id', '=', $userId);
            });
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     * @property-read \App\Models\Award $title
     */
    public function html()
    {
        $dataTable = $this->setBuilder('service agreements-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["service agreements-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
                }',
                /* 'buttons'      => ['excel'] */
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
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('customer', user_roles())
            ],
            __('modules.service agreements.contractNumber') => ['data' => 'contract_number', 'name' => 'contract_number', 'title' => '#'],
            __('app.subject') => ['data' => 'subject', 'name' => 'subject', 'exportable' => false, 'title' => __('app.subject')],
            __('app.menu.service agreement') . ' ' . __('app.subject') => ['data' => 'contract_subject', 'name' => 'subject', 'visible' => false, 'title' => __('app.menu.service agreement')],
            __('app.customer') => ['data' => 'customer.name', 'name' => 'customer.name', 'exportable' => false, 'title' => __('app.customer'), 'visible' => !in_array('customer', user_roles())],
            __('app.customers') => ['data' => 'client_name', 'name' => 'customer.name', 'visible' => false, 'title' => __('app.customers')],
            __('app.site') => ['data' => 'project_name', 'name' => 'site.project_name', 'title' => __('app.site')],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
            __('app.startDate') => ['data' => 'start_date', 'name' => 'start_date', 'title' => __('app.startDate')],
            __('app.endDate') => ['data' => 'end_date', 'name' => 'end_date', 'title' => __('app.endDate')],
            __('app.signature') => ['data' => 'signature', 'name' => 'signature', 'visible' => false, 'title' => __('app.signature')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Service Agreement()), $action);
    }
}
