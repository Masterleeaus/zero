<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\Service Job;
use App\Models\TaskboardColumn;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class TaskReportDataTable extends BaseDataTable
{

    private $viewUnassignedTasksPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
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
            ->editColumn('due_date', function ($row) {
                return Common::dateColor($row->due_date);
            })
            ->editColumn('users', function ($row) {
                if (count($row->users) == 0) {
                    return '--';
                }

                $members = '';

                foreach ($row->users as $member) {
                    $img = '<img data-toggle="tooltip" data-original-title="' . $member->name . '" src="' . $member->image_url . '">';

                    $members .= '<div class="taskEmployeeImg rounded-circle"><a href="' . route('cleaners.show', $member->id) . '">' . $img . '</a></div> ';
                }

                return $members;
            })
            ->addColumn('name', function ($row) {
                $members = [];

                foreach ($row->users as $member) {
                    $members[] = $member->name;
                }

                return implode(',', $members);
            })
            ->editColumn('clientName', function ($row) {
                return ($row->clientName) ? $row->clientName : '-';
            })
            ->addColumn('service job', function ($row) {
                return $row->heading;
            })
            ->editColumn('heading', function ($row) {
                $private = $pin = $timer = '';

                if ($row->is_private) {
                    $private = '<span class="badge badge-secondary"><i class="fa fa-lock"></i> ' . __('app.private') . '</span>';
                }

                if (($row->pinned_task)) {
                    $pin = '<span class="badge badge-secondary"><i class="fa fa-thumbtack"></i> ' . __('app.pinned') . '</span>';
                }

                if (count($row->activeTimerAll) > 0) {
                    $timer .= '<span class="badge badge-secondary"><i class="fa fa-clock"></i> ' . $row->activeTimer->timer . '</span>';
                }

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('service jobs.show', [$row->id]) . '" class="openRightModal">' . $row->heading . '</a></h5>
                    <p class="mb-0">' . $private . ' ' . $pin . ' ' . $timer . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('board_column', function ($row) {
                return '<i class="fa fa-circle mr-2" style="color: ' . $row->label_color . '"></i>' . $row->board_column;
            })
            ->addColumn('status', function ($row) {
                return $row->board_column;
            })
            ->editColumn('project_name', function ($row) {
                if (is_null($row->project_id)) {
                    return '-';
                }

                return '<a href="' . route('sites.show', $row->project_id) . '" class="text-darkest-grey">' . $row->project_name . '</a>';
            })
            ->editColumn('short_code', function ($row) {

                if (is_null($row->task_short_code)) {
                    return ' -- ';
                }

                return '<a href="' . route('service jobs.show', [$row->id]) . '" class="text-darkest-grey openRightModal">' . $row->task_short_code . '</a>';
            })
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['board_column', 'project_name', 'clientName', 'due_date', 'users', 'heading', 'short_code'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('created_image')
            ->removeColumn('label_color');
    }

    /**
     * @param Service Job $model
     * @return mixed
     */
    public function query(Service Job $model)
    {
        $request = $this->request();
        $startDate = null;
        $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $projectId = $request->projectId;
        $taskBoardColumn = TaskboardColumn::completeColumn();

        $model = $model->leftJoin('sites', 'sites.id', '=', 'service jobs.project_id')
            ->leftJoin('users as customer', 'customer.id', '=', 'sites.client_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'service jobs.board_column_id');

        if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
            $model->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->leftJoin('users as member', 'task_users.user_id', '=', 'member.id');
        } else {
            $model->join('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->join('users as member', 'task_users.user_id', '=', 'member.id');
        }

        $model->leftJoin('users as creator_user', 'creator_user.id', '=', 'service jobs.created_by')
            ->leftJoin('task_labels', 'task_labels.task_id', '=', 'service jobs.id')
            ->selectRaw('service jobs.id, service jobs.added_by, sites.project_name, sites.client_id, service jobs.heading, customer.name as clientName, creator_user.name as created_by, creator_user.image as created_image, service jobs.board_column_id,service jobs.task_short_code,
             service jobs.due_date, taskboard_columns.column_name as board_column, taskboard_columns.slug as board_column_slug, taskboard_columns.label_color,
              service jobs.project_id, service jobs.is_private ,( select count("id") from pinned where pinned.task_id = service jobs.id and pinned.user_id = ' . user()->id . ') as pinned_task')
            ->addSelect('service jobs.company_id') // Company_id is fetched so the we have fetch company relation with it)
            ->whereNull('sites.deleted_at')
            ->with('users', 'activeTimerAll', 'activeTimer')
            ->groupBy('service jobs.id');

        if ($startDate !== null && $endDate !== null) {
            $model->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(service jobs.`due_date`)'), [$startDate, $endDate]);

                $q->orWhereBetween(DB::raw('DATE(service jobs.`start_date`)'), [$startDate, $endDate]);
            });
        }

        if ($projectId != 0 && $projectId != null && $projectId != 'all') {
            $model->where('service jobs.project_id', '=', $projectId);
        }

        if ($request->clientID != '' && $request->clientID != null && $request->clientID != 'all') {
            $model->where('sites.client_id', '=', $request->clientID);
        }

        if ($request->assignedTo != '' && $request->assignedTo != null) {
            $model->where(function ($q) use ($request) {
                if ($request->assignedTo != 'all' && $request->assignedTo != 'unassigned') {
                    $q->where('task_users.user_id', '=', $request->assignedTo);
                }

                if ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all') {
                    $q->whereDoesntHave('users');
                    $q->orWhereHas('users');
                }
            });
        }

        if ($request->assignedBY != '' && $request->assignedBY != null && $request->assignedBY != 'all') {
            $model->where('creator_user.id', '=', $request->assignedBY);
        }

        if ($request->status != '' && $request->status != null && $request->status != 'all') {
            if ($request->status == 'not finished') {
                $model->where('service jobs.board_column_id', '<>', $taskBoardColumn->id);
            } else {
                $model->where('service jobs.board_column_id', '=', $request->status);
            }
        }

        if ($request->label != '' && $request->label != null && $request->label != 'all') {
            $model->where('task_labels.label_id', '=', $request->label);
        }

        if ($request->category_id != '' && $request->category_id != null && $request->category_id != 'all') {
            $model->where('service jobs.task_category_id', '=', $request->category_id);
        }

        if ($request->billable != '' && $request->billable != null && $request->billable != 'all') {
            $model->where('service jobs.billable', '=', $request->billable);
        }

        if ($request->searchText != '') {
            $safeTerm = Common::safeString(request('searchText'));
            $model->where(function ($query) use ($safeTerm) {
                $query->where('service jobs.heading', 'like', '%' . $safeTerm . '%')
                    ->orWhere('member.name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('sites.project_name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('service jobs.task_short_code', 'like', '%' . $safeTerm . '%');
            });
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
        $dataTable = $this->setBuilder('allTasks-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["allTasks-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#allTasks-table .select-picker").selectpicker();
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
            __('modules.taskCode') => ['data' => 'short_code', 'name' => 'task_short_code', 'title' => __('modules.taskCode')],
            __('app.service job') => ['data' => 'heading', 'name' => 'heading', 'exportable' => false, 'title' => __('app.service job')],
            __('app.menu.service jobs') => ['data' => 'service job', 'name' => 'heading', 'visible' => false, 'title' => __('app.menu.service jobs')],
            __('app.site') => ['data' => 'project_name', 'name' => 'sites.project_name', 'title' => __('app.site')],
            __('app.customer') => ['data' => 'clientName', 'name' => 'customer.name', 'title' => __('app.customer')],
            __('modules.service jobs.assigned') => ['data' => 'name', 'name' => 'name', 'visible' => false, 'title' => __('modules.service jobs.assigned')],
            __('app.dueDate') => ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')],
            __('modules.service jobs.assignTo') => ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.service jobs.assignTo')],
            __('app.service job') . ' ' . __('app.status') => ['data' => 'status', 'name' => 'board_column', 'visible' => false, 'title' => __('app.service job')],
            __('app.columnStatus') => ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')]
        ];
    }
}
