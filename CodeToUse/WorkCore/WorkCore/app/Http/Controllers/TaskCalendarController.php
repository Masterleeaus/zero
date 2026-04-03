<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Service Job;
use App\Models\TaskboardColumn;
use App\Models\TaskCategory;
use App\Models\TaskLabelList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper\Common;

class TaskCalendarController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskCalendar';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('service jobs', $this->user->modules));
            $this->viewTaskPermission = user()->permission('view_tasks');
            $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->sites = Site::allProjects();
        $this->customers = User::allClients();
        $this->cleaners = User::allEmployees();
        $this->taskCategories = TaskCategory::all();
        $this->taskLabels  = TaskLabelList::all();
        $this->taskBoardStatus = TaskboardColumn::all();

        if (request('start') && request('end')) {
            $startDate = Carbon::parse(request('start'))->format('Y-m-d');
            $endDate = Carbon::parse(request('end'))->format('Y-m-d');
            $projectId = $request->projectID;
            $taskBoardColumn = TaskboardColumn::completeColumn();

            $model = Service Job::leftJoin('sites', 'sites.id', '=', 'service jobs.project_id')
                ->leftJoin('users as customer', 'customer.id', '=', 'sites.client_id')
                ->join('taskboard_columns', 'taskboard_columns.id', '=', 'service jobs.board_column_id');

            if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
                $model->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
                    ->leftJoin('users', 'task_users.user_id', '=', 'users.id');
            } else {
                $model->join('task_users', 'task_users.task_id', '=', 'service jobs.id')
                    ->join('users', 'task_users.user_id', '=', 'users.id');
            }

            $model->leftJoin('users as creator_user', 'creator_user.id', '=', 'service jobs.created_by')
                ->leftJoin('task_labels', 'task_labels.task_id', '=', 'service jobs.id')
                ->select('service jobs.*')
                ->whereNull('sites.deleted_at')
                ->with('boardColumn', 'users')
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

            if ($request->assignedTo != '' && $request->assignedTo != null && $request->assignedTo != 'all') {
                $model->where('task_users.user_id', '=', $request->assignedTo);
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

            if ($request->categoryId != '' && $request->categoryId != null && $request->categoryId != 'all') {
                $model->where('service jobs.task_category_id', '=', $request->categoryId);
            }

            if ($request->billable != '' && $request->billable != null && $request->billable != 'all') {
                $model->where('service jobs.billable', '=', $request->billable);
            }

            if ($request->searchText != '') {
                $model->where(function ($query) {
                    $safeTerm = Common::safeString(request('searchText'));
                    $query->where('service jobs.heading', 'like', '%' . $safeTerm . '%')
                        ->orWhere('member.name', 'like', '%' . $safeTerm . '%')
                        ->orWhere('sites.project_name', 'like', '%' . $safeTerm . '%');
                });
            }

            if ($this->viewTaskPermission == 'owned') {
                $model->where(function ($q1) use ($request) {
                    $q1->where('task_users.user_id', '=', user()->id);

                    if (in_array('customer', user_roles())) {
                        $q1->orWhere('sites.client_id', '=', user()->id);
                    }

                    if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
                        $q1->orWhereDoesntHave('users');
                    }
                });
            }

            if ($this->viewTaskPermission == 'added') {
                $model->where('service jobs.added_by', '=', user()->id);
            }

            if ($this->viewTaskPermission == 'both') {
                $model->where(function ($q1) use ($request) {
                    $q1->where('task_users.user_id', '=', user()->id);

                    $q1->orWhere('service jobs.added_by', '=', user()->id);

                    if (in_array('customer', user_roles())) {
                        $q1->orWhere('sites.client_id', '=', user()->id);
                    }

                    if ($this->viewUnassignedTasksPermission == 'all' && !in_array('customer', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
                        $q1->orWhereDoesntHave('users');
                    }
                });
            }

            $service jobs = $model->get();

            $taskData = array();

            foreach ($service jobs as $key => $value) {
                $taskData[] = [
                    'id' => $value->id,
                    'title' => $value->heading,
                    'start' => $value->start_date->format('Y-m-d'),
                    'end' => (!is_null($value->due_date) ? $value->due_date->format('Y-m-d') : $value->start_date->format('Y-m-d')),
                    'color' => $value->boardColumn->label_color
                ];
            }

            return $taskData;
        }

        $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        if (!in_array('admin', user_roles()) && in_array('cleaner', user_roles())) {
            $user = User::findOrFail(user()->id);
            $this->waitingApprovalCount = $user->service jobs()->where('board_column_id', $taskBoardColumn->id)->count();
        } else {
            $this->waitingApprovalCount = Service Job::where('board_column_id', $taskBoardColumn->id)->count();
        }
        return view('service jobs.calendar', $this->data);
    }
}
