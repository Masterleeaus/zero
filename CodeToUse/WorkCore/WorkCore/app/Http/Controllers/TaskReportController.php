<?php

namespace App\Http\Controllers;

use App\DataTables\consolidatedTaskReportDataTable;
use App\DataTables\EmployeeWiseTaskDataTable;
use App\DataTables\TaskReportDataTable;
use App\Helper\Reply;
use App\Models\Site;
use App\Models\Service Job;
use App\Models\TaskboardColumn;
use App\Models\TaskCategory;
use App\Models\TaskLabelList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper\Common;

class TaskReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskReport';
    }

    public function index(TaskReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_task_report') != 'all');

        if (!request()->ajax()) {
            $this->sites = Site::allProjects();
            $this->customers = User::allClients();
            $this->cleaners = User::allEmployees();
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
        }

        return $dataTable->render('reports.service jobs.index', $this->data);
    }

    public function taskChartData(Request $request)
    {
        $taskStatus = TaskboardColumn::all();

        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        $startDate = $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $projectId = $request->projectId;
        $taskBoardColumn = TaskboardColumn::completeColumn();

        foreach ($taskStatus as $label) {
            $model = Service Job::leftJoin('sites', 'sites.id', '=', 'service jobs.project_id')
                ->leftJoin('users as creator_user', 'creator_user.id', '=', 'service jobs.created_by')
                ->leftJoin('task_labels', 'task_labels.task_id', '=', 'service jobs.id')
                ->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->where('service jobs.board_column_id', $label->id);

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

            if ($request->category_id != '' && $request->category_id != null && $request->category_id != 'all') {
                $model->where('service jobs.task_category_id', '=', $request->category_id);
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

            $data['values'][] = $model->count();
        }

        $this->chartData = $data;
        $html = view('reports.service jobs.chart', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }

    public function employeeWiseTaskReport(EmployeeWiseTaskDataTable $dataTable)
    {
        abort_403(user()->permission('view_task_report') != 'all');
        $this->sites = Site::allProjects(true);

        $this->pageTitle = 'modules.service jobs.employeeWiseTaskReport';

        if (!request()->ajax()) {
            $this->sites = Site::allProjects();
            $this->customers = User::allClients();
            $this->cleaners = User::allEmployees();
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
        }

        return $dataTable->render('reports.service jobs.cleaner-wise-service job', $this->data);
    }

    public function consolidatedTaskReport(consolidatedTaskReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_task_report') != 'all');
        $this->sites = Site::allProjects(true);

        $this->pageTitle = 'modules.service jobs.consolidatedTaskReport';

        if (!request()->ajax()) {
            $this->sites = Site::allProjects();
            $this->customers = User::allClients();
            $this->cleaners = User::allEmployees();
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
        }

        return $dataTable->render('reports.service jobs.consolidated-service job-report', $this->data);
    }
}
