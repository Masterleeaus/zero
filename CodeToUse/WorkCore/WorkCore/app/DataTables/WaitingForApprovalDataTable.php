<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\BaseModel;
use App\Models\Service Job;
use App\Models\CustomField;
use App\Models\TaskboardColumn;
use App\Models\Site;
use App\Models\CustomFieldGroup;
use App\Models\TaskSetting;
use Carbon\CarbonInterval;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class WaitingForApprovalDataTable extends BaseDataTable
{

    private $editTaskPermission;
    private $deleteTaskPermission;
    private $viewTaskPermission;
    private $changeStatusPermission;
    private $viewUnassignedTasksPermission;
    private $hasTimelogModule;
    private $projectView;

    public function __construct($projectView = false)
    {
        parent::__construct();

        $this->editTaskPermission = user()->permission('edit_tasks');
        $this->deleteTaskPermission = user()->permission('delete_tasks');
        $this->viewTaskPermission = user()->permission('view_tasks');
        $this->changeStatusPermission = user()->permission('change_status');
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
        $this->hasTimelogModule = (in_array('timelogs', user_modules()));
        $this->viewProjectTaskPermission = user()->permission('view_project_tasks');
        $this->projectView = $projectView;
        $this->tabUrl = ($this->projectView == true) ? '?tab=site' : '';
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $taskBoardColumns = TaskboardColumn::orderBy('priority')->get();

        $datatables = datatables()->eloquent($query);
        $datatables->addColumn('check', fn($row) => $this->checkBox($row, $row->activeTimer ? true : false));
        $datatables->addColumn(
            'action',
            function ($row) {

                $userRoles = user_roles();
                $isAdmin = in_array('admin', $userRoles);
                $isEmployee = in_array('cleaner', $userRoles);

                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('service jobs.show', [$row->id]) . $this->tabUrl . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($row->canEditTicket()) {
                    if ($isAdmin || ($row->approval_send == 0 && $isEmployee && !$isAdmin) || $row->project_admin == user()->id) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('service jobs.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                    }
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('service jobs.create') . '?duplicate_task=' . $row->id . '">
                                <i class="fa fa-clone"></i>
                                ' . trans('app.duplicate') . '
                            </a>';
                }

                if ($row->canDeleteTicket()) {
                    if ($isAdmin || ($row->approval_send == 0 && $isEmployee && !$isAdmin)) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-active-running = "' . ($row->activeTimer ? true : false) . '" data-user-id="' . $row->id . '">

                                    <i class="fa fa-trash mr-2"></i>
                                    ' . trans('app.delete') . '
                                </a>';
                    }
                }


                $action .= '</div>
                    </div>
                </div>';

                return $action;
            }
        );

        $datatables->editColumn('start_date', fn($row) => Common::dateColor($row->start_date, false));
        $datatables->editColumn('due_date', fn($row) => Common::dateColor($row->due_date));

        $datatables->editColumn('completed_on', fn($row) => Common::dateColor($row->completed_on));

        $datatables->editColumn('users', function ($row) {

            if (count($row->users) == 0) {
                return '--';
            }

            $key = '';
            $members = '<div class="position-relative">';

            foreach ($row->users as $key => $member) {
                if ($key < 4) {
                    $img = '<img data-toggle="tooltip" data-original-title="' . $member->name . '" src="' . $member->image_url . '">';
                    $position = $key > 0 ? 'position-absolute' : '';

                    $members .= '<div class="taskEmployeeImg rounded-circle ' . $position . '" style="left:  ' . ($key * 13) . 'px"><a href="' . route('cleaners.show', $member->id) . '">' . $img . '</a></div> ';
                }
            }

            if (count($row->users) > 4 && $key) {
                $members .= '<div class="taskEmployeeImg more-user-count text-center rounded-circle border bg-amt-grey position-absolute" style="left:  ' . (($key - 1) * 13) . 'px"><a href="' . route('service jobs.show', [$row->id]) . '" class="text-dark f-10">+' . (count($row->users) - 4) . '</a></div> ';
            }

            $members .= '</div>';

            return $members;
        });

        $datatables->editColumn('short_code', function ($row) {

            if (is_null($row->task_short_code)) {
                return ' -- ';
            }

            return '<a href="' . route('service jobs.show', [$row->id]) . $this->tabUrl . '" class="text-darkest-grey openRightModal">' . $row->task_short_code . '</a>';
        });

        $datatables->addColumn('name', function ($row) {
            $members = [];

            foreach ($row->users as $member) {
                $members[] = $member->name;
            }

            return implode(',', $members);
        });

        $datatables->editColumn('clientName', fn($row) => $row->clientName ?: '--');
        $datatables->addColumn('service job', fn($row) => $row->heading);
        $datatables->addColumn('task_project_name', fn($row) => !is_null($row->project_id) ? $row->project_name : '--');

        $datatables->addColumn('estimateTime', function ($row) {

            $time = $row->estimate_hours * 60 + $row->estimate_minutes;
            return CarbonInterval::formatHuman($time);
        });

        $datatables->addColumn('timeLogged', function ($row) {

            $estimatedTime = $row->estimate_hours * 60 + $row->estimate_minutes;

            $timeLog = '--';
            $loggedTime = '';

            if ($row->timeLogged) {
                $totalMinutes = $row->timeLogged->sum('total_minutes');

                $breakMinutes = $row->breakMinutes();

                $loggedHours = $totalMinutes - $breakMinutes;

                // Convert total minutes to hours and minutes
                $hours = intdiv($loggedHours, 60);
                $minutes = $loggedHours % 60;

                // Format output based on hours and minutes
                $timeLog = $hours > 0
                    ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
                    : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');
            }

            if ($estimatedTime < $loggedHours) {
                $loggedTime = '<span class="text-danger">' . $timeLog . '</span>';
            } else {
                $loggedTime = '<span>' . $timeLog . '</span>';
            }
            return $loggedTime;
        });
        $datatables->editColumn('heading', function ($row) {
            $subTask = $labels = $private = $pin = $timer = '';

            if ($row->is_private) {
                $private = '<span class="badge badge-secondary mr-1"><i class="fa fa-lock"></i> ' . __('app.private') . '</span>';
            }

            if (($row->pinned_task)) {
                $pin = '<span class="badge badge-secondary mr-1"><i class="fa fa-thumbtack"></i> ' . __('app.pinned') . '</span>';
            }

            if ($row->active_timer_all_count > 1) {
                $timer .= '<span class="badge badge-primary mr-1" ><i class="fa fa-clock"></i> ' . $row->active_timer_all_count . ' ' . __('modules.sites.activeTimers') . '</span>';
            }

            if ($row->activeTimer && $row->active_timer_all_count == 1) {
                $timer .= '<span class="badge badge-primary mr-1" data-toggle="tooltip" data-original-title="' . __('modules.sites.activeTimers') . '" ><i class="fa fa-clock"></i> ' . $row->activeTimer->timer . '</span>';
            }

            if ($row->subtasks_count > 0) {
                $subTask .= '<a href="' . route('service jobs.show', [$row->id]) . '?view=sub_task" class="openRightModal"><span class="border rounded p-1 f-11 mr-1 text-dark-grey" data-toggle="tooltip" data-original-title="' . __('modules.service jobs.subTask') . '"><i class="bi bi-diagram-2"></i> ' . $row->completed_subtasks_count . '/' . $row->subtasks_count . '</span></a>';
            }

            foreach ($row->labels as $label) {
                $labels .= '<span class="badge badge-secondary mr-1" style="background-color: ' . $label->label_color . '">' . $label->label_name . '</span>';
            }

            $name = '';

            if (!is_null($row->project_id) && !is_null($row->id)) {
                $name .= '<h5 class="f-13 text-darkest-grey mb-0">' . $row->heading . '</h5><div class="text-muted f-11">' . $row->project_name . '</div>';
            } else if (!is_null($row->id)) {
                $name .= '<h5 class="f-13 text-darkest-grey mb-0 mr-1">' . $row->heading . '</h5>';
            }

            if ($row->repeat) {
                $name .= '<span class="badge badge-primary">' . __('modules.events.repeat') . '</span>';
            }

            return BaseModel::clickAbleLink(route('service jobs.show', [$row->id]) . $this->tabUrl, $name, $subTask . ' ' . $private . ' ' . $pin . ' ' . $timer . ' ' . $labels);
        });
        $datatables->editColumn('board_column', function ($row) use ($taskBoardColumns) {
            $taskUsers = $row->users->pluck('id')->toArray();

            if (
                $this->changeStatusPermission == 'all'
                || ($this->changeStatusPermission == 'added' && $row->added_by == user()->id)
                || ($this->changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($this->changeStatusPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id))
                || ($row->project_admin == user()->id)
            ) {
                // Check if approval_send is 1, then disable the select dropdown
                if ($row->approval_send == 1 && $row->need_approval_by_admin == 1 && (in_array('cleaner', user_roles()) && $row->project_admin != user()->id) && !in_array('admin', user_roles())) {
                    return '<span class="p-2 disabled-select" data-toggle="tooltip" title=""><i class="fa fa-circle mr-1 text-yellow"
                            style="color: ' . $row->boardColumn->label_color . '"></i>' . $row->boardColumn->column_name . '</span>';
                } else {
                    $status = '<select class="form-control select-picker change-status" data-size="3" data-service job-id="' . $row->id . '">';

                    foreach ($taskBoardColumns as $item) {
                        $status .= '<option ';

                        if ($item->id == $row->board_column_id) {
                            $status .= 'selected';
                        }

                        $status .= '  data-content="<i class=\'fa fa-circle mr-2\' style=\'color: ' . $item->label_color . '\'></i> ' . $item->column_name . '" value="' . $item->slug . '">' . $item->column_name . '</option>';
                    }

                    $status .= '</select>';

                    return $status;
                }
            }

            return '<span class="p-2"><i class="fa fa-circle mr-1 text-yellow"
                    style="color: ' . $row->boardColumn->label_color . '"></i>' . $row->boardColumn->column_name . '</span>';
        });
        $datatables->addColumn('status', fn($row) => $row->boardColumn->column_name);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->setRowClass(fn($row) => $row->pinned_task ? 'alert-primary' : '');
        $datatables->removeColumn('project_id');
        $datatables->removeColumn('image');
        $datatables->removeColumn('created_image');
        $datatables->removeColumn('label_color');

        // CustomField For export
        $customFieldColumns = CustomField::customFieldData($datatables, Service Job::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['short_code', 'board_column', 'completed_on', 'action', 'clientName', 'due_date', 'users', 'heading', 'check', 'timeLogged', 'timer', 'start_date'], $customFieldColumns));

        return $datatables;
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
        $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        $projectIds = Site::where('project_admin', user()->id)->pluck('id');

        $model = $model->leftJoin('sites', 'sites.id', '=', 'service jobs.project_id')
            ->leftJoin('users as customer', 'customer.id', '=', 'sites.client_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'service jobs.board_column_id')
            ->leftJoin('mention_users', 'mention_users.task_id', 'service jobs.id');

        if (($this->viewUnassignedTasksPermission == 'all'
                && !in_array('customer', user_roles())
                && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all'))
            || ($request->has('project_admin') && $request->project_admin == 1)
        ) {
            $model->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->leftJoin('users as member', 'task_users.user_id', '=', 'member.id');
        } else {
            $model->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->leftJoin('users as member', 'task_users.user_id', '=', 'member.id');
        }

        $model->leftJoin('users as creator_user', 'creator_user.id', '=', 'service jobs.created_by')
            ->leftJoin('task_labels', 'task_labels.task_id', '=', 'service jobs.id')
            ->selectRaw(
                'service jobs.id, service jobs.approval_send, service jobs.estimate_hours, service jobs.estimate_minutes, service jobs.completed_on, service jobs.task_short_code, service jobs.start_date, service jobs.added_by, sites.need_approval_by_admin, sites.project_name, sites.project_admin, service jobs.heading, service jobs.repeat, customer.name as clientName, creator_user.name as created_by, creator_user.image as created_image, service jobs.board_column_id,
             service jobs.due_date, taskboard_columns.column_name as board_column, taskboard_columns.slug, taskboard_columns.label_color,
              service jobs.project_id, service jobs.is_private ,( select count("id") from pinned where pinned.task_id = service jobs.id and pinned.user_id = ' . user()->id . ') as pinned_task'
            )
            ->addSelect('service jobs.company_id') // Company_id is fetched so the we have fetch company relation with it)
            ->with('users', 'activeTimerAll', 'boardColumn', 'activeTimer', 'timeLogged', 'timeLogged.breaks', 'userActiveTimer', 'userActiveTimer.activeBreak', 'labels', 'taskUsers')
            ->withCount('activeTimerAll', 'completedSubtasks', 'checklists')
            ->groupBy('service jobs.id');

        if ($request->pinned == 'pinned') {
            $model->join('pinned', 'pinned.task_id', 'service jobs.id');
            $model->where('pinned.user_id', user()->id);
        }

        if (!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && $projectIds->isEmpty())) {
            if ($request->pinned == 'private') {
                $model->where(
                    function ($q2) {
                        $q2->where('service jobs.is_private', 1);
                        $q2->where(
                            function ($q4) {
                                $q4->where('task_users.user_id', user()->id);
                                $q4->orWhere('service jobs.added_by', user()->id);
                            }
                        );
                    }
                );
            } else {
                $model->where(
                    function ($q) {
                        $q->where('service jobs.is_private', 0);
                        $q->orWhere(
                            function ($q2) {
                                $q2->where('service jobs.is_private', 1);
                                $q2->where(
                                    function ($q5) {
                                        $q5->where('task_users.user_id', user()->id);
                                        $q5->orWhere('service jobs.added_by', user()->id);
                                    }
                                );
                            }
                        );
                    }
                );
            }
        }


        if ($request->assignedTo == 'unassigned' && $this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles())) {
            $model->whereDoesntHave('users');
        }

        if ($startDate !== null && $endDate !== null) {
            $model->where(
                function ($q) use ($startDate, $endDate) {
                    if (request()->date_filter_on == 'due_date') {
                        $q->whereBetween(DB::raw('DATE(service jobs.`due_date`)'), [$startDate, $endDate]);
                    } elseif (request()->date_filter_on == 'start_date') {
                        $q->whereBetween(DB::raw('DATE(service jobs.`start_date`)'), [$startDate, $endDate]);
                    } elseif (request()->date_filter_on == 'completed_on') {
                        $q->whereBetween(DB::raw('DATE(service jobs.`completed_on`)'), [$startDate, $endDate]);
                    }
                }
            );
        }

        if ($request->overdue == 'yes' && $request->status != 'all') {
            $model->where(DB::raw('DATE(service jobs.`due_date`)'), '<', now(company()->timezone)->toDateString());
        }

        if ($projectId != 0 && $projectId != null && $projectId != 'all') {
            $model->where('service jobs.project_id', '=', $projectId);
        }

        if ($request->clientID != '' && $request->clientID != null && $request->clientID != 'all') {
            $model->where('sites.client_id', '=', $request->clientID);
        }

        if ($request->assignedTo != '' && $request->assignedTo != null && $request->assignedTo != 'all' && $request->assignedTo != 'unassigned') {
            $model->where('task_users.user_id', '=', $request->assignedTo);
        }

        if (($request->has('project_admin') && $request->project_admin != 1) || !$request->has('project_admin')) {
            if ($this->viewTaskPermission == 'owned' && $this->projectView == false) {
                $model->where(
                    function ($q) use ($request) {
                        $q->where('task_users.user_id', '=', user()->id);
                        $q->orWhere('mention_users.user_id', user()->id);

                        if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && $request->assignedTo == 'all') {
                            $q->orWhereDoesntHave('users');
                        }

                        if (in_array('customer', user_roles())) {
                            $q->orWhere('sites.client_id', '=', user()->id);
                        }
                    }
                );

                if ($projectId != 0 && $projectId != null && $projectId != 'all' && !in_array('customer', user_roles())) {
                    $model->where(
                        function ($q) {
                            $q->where('sites.project_admin', '<>', user()->id)
                                ->orWhere('mention_users.user_id', user()->id);
                        }
                    );
                }
            }

            if ($this->viewTaskPermission == 'added' && $this->projectView == false) {
                $model->where(
                    function ($q) {
                        $q->where('service jobs.added_by', '=', user()->id)
                            ->orWhere('mention_users.user_id', user()->id);
                    }
                );
            }

            if ($this->viewTaskPermission == 'both' && $this->projectView == false) {
                $model->where(
                    function ($q) use ($request, $projectIds) {
                        $q->where('task_users.user_id', '=', user()->id);
                        $q->orwhereIn('service jobs.project_id', $projectIds);
                        $q->orWhere('service jobs.added_by', '=', user()->id)
                            ->orWhere('mention_users.user_id', user()->id);

                        if (in_array('customer', user_roles())) {
                            $q->orWhere('sites.client_id', '=', user()->id);
                        }

                        if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
                            $q->orWhereDoesntHave('users');
                        }
                    }
                );
            }
        }

        if ($request->assignedBY != '' && $request->assignedBY != null && $request->assignedBY != 'all') {
            $model->where('creator_user.id', '=', $request->assignedBY);
        }

        if (!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && $projectIds->isEmpty())) {

            $model->where('task_users.user_id', '=', user()->id)->where('service jobs.board_column_id', '=', $taskBoardColumn->id);
        } else {

            $model->where('service jobs.board_column_id', '=', $taskBoardColumn->id);
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

        if ($request->milestone_id != '' && $request->milestone_id != null && $request->milestone_id != 'all') {
            $model->where('service jobs.milestone_id', $request->milestone_id);
        }

        if ($request->searchText != '') {
            $model->where(
                function ($query) {
                    $safeTerm = Common::safeString(request('searchText'));
                    $query->where('service jobs.heading', 'like', '%' . $safeTerm . '%')
                        ->orWhere('member.name', 'like', '%' . $safeTerm . '%')
                        ->orWhere('sites.project_name', 'like', '%' . $safeTerm . '%')
                        ->orWhere('sites.project_short_code', 'like', '%' . $safeTerm . '%')
                        ->orWhere('service jobs.task_short_code', 'like', '%' . $safeTerm . '%');
                }
            );
        }

        if ($request->trashedData == 'true') {
            $model->whereNotNull('sites.deleted_at');
        } else {
            $model->whereNull('sites.deleted_at');
        }

        if ($request->type == 'public') {
            $model->where('service jobs.is_private', 0);
        }

        $model->orderbyRaw('pinned_task desc');

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
            ->parameters(
                [
                    'initComplete' => 'function () {
                   window.LaravelDataTables["allTasks-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                    'fnDrawCallback' => 'function( oSettings ) {
                    $("#allTasks-table .select-picker").selectpicker();
                    $(".bs-tooltip-top").removeClass("show");
                }',
                ]
            );

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
        $taskSettings = TaskSetting::first();

        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
            ],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('modules.taskCode') => ['data' => 'short_code', 'name' => 'task_short_code', 'title' => __('modules.taskCode')]
        ];

        // if (in_array('timelogs', user_modules())) {
        //     $data[__('app.timer') . ' '] = ['data' => 'timer', 'name' => 'timer', 'exportable' => false, 'searchable' => false, 'sortable' => false, 'title' => __('app.timer'), 'class' => 'text-right'];
        // }

        $data2 = [

            __('app.service job') => ['data' => 'heading', 'name' => 'heading', 'exportable' => false, 'title' => __('app.service job')],
            __('app.menu.service jobs') . ' ' => ['data' => 'service job', 'name' => 'heading', 'visible' => false, 'title' => __('app.menu.service jobs')],
            __('app.site') => ['data' => 'task_project_name', 'visible' => false, 'name' => 'task_project_name', 'title' => __('app.site')],
            __('modules.service jobs.assigned') => ['data' => 'name', 'name' => 'name', 'visible' => false, 'title' => __('modules.service jobs.assigned')],
            __('app.completedOn') => ['data' => 'completed_on', 'name' => 'completed_on', 'title' => __('app.completedOn')]
        ];

        $data = array_merge($data, $data2);

        if (in_array('customer', user_roles())) {

            if (in_array('customer', user_roles()) && $taskSettings->start_date == 'yes') {
                $data[__('app.startDate')] = ['data' => 'start_date', 'name' => 'start_date', 'title' => __('app.startDate')];
            }

            if ($taskSettings->due_date == 'yes') {
                $data[__('app.dueDate')] = ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')];
            }

            if ($taskSettings->time_estimate == 'yes' && in_array('timelogs', user_modules())) {
                $data[__('modules.service jobs.estimateTime')] = ['data' => 'estimateTime', 'name' => 'estimateTime', 'title' => __('modules.service jobs.estimateTime')];
            }

            if ($taskSettings->hours_logged == 'yes' && in_array('timelogs', user_modules())) {
                $data[__('modules.cleaners.hoursLogged')] = ['data' => 'timeLogged', 'name' => 'timeLogged', 'title' => __('modules.cleaners.hoursLogged')];
            }

            if ($taskSettings->assigned_to == 'yes') {
                $data[__('modules.service jobs.assignTo')] = ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.service jobs.assignTo')];
            }

            if ($taskSettings->status == 'yes') {
                $data[__('app.columnStatus')] = ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')];
            }
        } else {
            $data[__('app.startDate')] = ['data' => 'start_date', 'name' => 'start_date', 'title' => __('app.startDate')];
            $data[__('app.dueDate')] = ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')];

            if ($taskSettings->time_estimate == 'yes' && in_array('timelogs', user_modules())) {
                $data[__('modules.service jobs.estimateTime')] = ['data' => 'estimateTime', 'name' => 'estimateTime', 'title' => __('modules.service jobs.estimateTime')];
            }

            if (in_array('timelogs', user_modules())) {
                $data[__('modules.cleaners.hoursLogged')] = ['data' => 'timeLogged', 'name' => 'timeLogged', 'title' => __('modules.cleaners.hoursLogged')];
            }

            $data[__('modules.service jobs.assignTo')] = ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.service jobs.assignTo')];
            $data[__('app.columnStatus')] = ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')];
        }

        $data[__('app.service job') . ' ' . __('app.status')] = ['data' => 'status', 'name' => 'board_column_id', 'visible' => false, 'title' => __('app.service job')];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Service Job()), $action);
    }
}
