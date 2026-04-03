<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Service Job;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Leave;
use App\Models\Pinned;
use App\Models\Site;
use App\Models\SubTask;
use App\Models\TaskFile;
use App\Models\TaskUser;
use App\Http\Requests\Service Jobs\ActionTask;
use App\Models\TaskComment;
use App\Models\BaseModel;
use App\Models\TaskLabel;
use App\Models\SubTaskFile;
use App\Models\TaskSetting;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use App\Models\TaskLabelList;
use App\Models\ProjectTimeLog;
use App\Models\TaskboardColumn;
use App\Traits\ProjectProgress;
use App\Models\ProjectMilestone;
use App\Events\TaskReminderEvent;
use App\DataTables\TasksDataTable;
use App\DataTables\WaitingForApprovalDataTable;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use App\Http\Requests\Service Jobs\StoreTask;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Service Jobs\UpdateTask;
use App\Events\TaskEvent;
use App\Helper\UserService;
use App\Models\ClientContact;

class TaskController extends AccountBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.service jobs';
        $this->middleware(
            function ($request, $next) {
                abort_403(!in_array('service jobs', $this->user->modules));

                return $next($request);
            }
        );
    }

    public function index(TasksDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_tasks');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->assignedTo = request()->assignedTo;

            if (request()->has('assignee') && request()->assignee == 'me') {
                $this->assignedTo = user()->id;
            }

            $this->sites = Site::allProjects();

            if (in_array('customer', user_roles())) {
                $this->customers = User::customer();
            }
            else {
                $this->customers = User::allClients();
            }

            $this->cleaners = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
            $this->milestones = ProjectMilestone::all();

            $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();

            $projectIds = Site::where('project_admin', user()->id)->pluck('id');

            if (!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && $projectIds->isEmpty())) {
                $user = User::findOrFail(user()->id);
                $this->waitingApprovalCount = $user->service jobs()->where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }elseif(!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && !$projectIds->isEmpty())) {
                $this->waitingApprovalCount = Service Job::whereIn('project_id', $projectIds)->where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }else{
                $this->waitingApprovalCount = Service Job::where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }
        }

        return $dataTable->render('service jobs.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('team chat.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('team chat.updateSuccess'));
        case 'milestone':
            $this->changeMilestones($request);

            return Reply::success(__('team chat.updateSuccess'));
        default:
            return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_tasks') != 'all');

        $ids = explode(',', $request->row_ids);

        Service Job::whereIn('id', $ids)->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_tasks') != 'all');

        $taskBoardColumn = TaskboardColumn::findOrFail(request()->status);

        // Update service jobs based on the requested status
        $taskIds = explode(',', $request->row_ids);

        if ($taskBoardColumn && $taskBoardColumn->slug == 'completed') {
            Service Job::whereIn('id', $taskIds)->update([
                'status' => 'completed',
                'board_column_id' => $request->status,
                'completed_on' => now()->format('Y-m-d')
            ]);
        }
        else {
            Service Job::whereIn('id', $taskIds)->update(['board_column_id' => $request->status]);
        }

    }

    public function changeMilestones($request)
    {
        abort_403(user()->permission('edit_tasks') != 'all');

        $taskIds = explode(',', $request->row_ids);

        Service Job::whereIn('id', $taskIds)->update([
            'milestone_id' => $request->milestone
        ]);
    }

    public function changeStatus(Request $request)
    {
        $taskId = $request->taskId;
        $status = $request->status;

        $service job = Service Job::withTrashed()->with('site', 'users')->findOrFail($taskId);

        $taskUsers = $service job->users->pluck('id')->toArray();

        $this->editPermission = user()->permission('edit_tasks');
        $this->changeStatusPermission = user()->permission('change_status');
        abort_403(
            !(
                $this->changeStatusPermission == 'all'
                || ($this->changeStatusPermission == 'added' && $service job->added_by == user()->id)
                || ($this->changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($this->changeStatusPermission == 'both' && (in_array(user()->id, $taskUsers) || $service job->added_by == user()->id))
                || ($service job->site && $service job->site->project_admin == user()->id)
            )
        );

        $taskBoardColumn = TaskboardColumn::where('slug', $status)->first();
        $service job->board_column_id = $taskBoardColumn->id;

        if ($service job->status === 'completed' && $status !== 'completed') {
            $service job->approval_send = 0; // Reset approval_send to 0
        }

        if ($taskBoardColumn->slug == 'completed') {
            $service job->status = 'completed';
            $service job->completed_on = now()->format('Y-m-d');
        }
        else {
            $service job->completed_on = null;
        }

        if ($service job->trashed()) {
            $service job->saveQuietly();
        }
        else {
            $service job->save();
        }

        
        if ($service job->project_id != null) {
            
            if ($service job->site->calculate_task_progress == 'task_completion') {
                // Calculate site progress if enabled
                
                $this->calculateProjectProgress($service job->project_id, 'true');
            } elseif ($service job->site->calculate_task_progress == 'project_total_time') {
                // Calculate site progress based on time
                $this->calculateProjectProgressByTime($service job->project_id);
            }
        }

        $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

        $clockHtml = view('sections.timer_clock', $this->data)->render();

        return Reply::successWithData(__('team chat.updateSuccess'), ['clockHtml' => $clockHtml]);

    }

    public function milestoneChange(Request $request)
    {
        $editTaskPermission = user()->permission('edit_tasks');
        $editMilestonePermission = user()->permission('edit_project_milestones');

        $taskId = $request->taskId;
        $milestoneId = $request->milestone_id;

        $service job = Service Job::withTrashed()->with('site', 'users')->findOrFail($taskId);
        $taskUsers = $service job->users->pluck('id')->toArray();

        abort_403(
            !(
                ($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $service job->added_by == user()->id)
                || ($service job->site && ($service job->site->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $service job->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('customer', user_roles()) && $service job->site && ($service job->site->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('customer', user_roles()) && ($service job->site && ($service job->site->client_id == user()->id)) || $service job->added_by == user()->id))
                ) &&(
                    $editMilestonePermission == 'all'
                    || ($editMilestonePermission == 'added' && $service job->added_by == user()->id)
                    || ($editMilestonePermission == 'owned' && in_array(user()->id, $taskUsers))
                    || ($editMilestonePermission == 'owned' && (in_array('customer', user_roles()) && $service job->site && ($service job->site->client_id == user()->id)))
                )
            )
        );

        $service job->milestone_id = $milestoneId;
        $service job->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function destroy(Request $request, $id)
    {
        $service job = Service Job::with('site')->findOrFail($id);

        $this->deletePermission = user()->permission('delete_tasks');

        $taskUsers = $service job->users->pluck('id')->toArray();

        abort_403(
            !($this->deletePermission == 'all'
                || ($this->deletePermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($service job->site && ($service job->site->project_admin == user()->id))
                || ($this->deletePermission == 'added' && $service job->added_by == user()->id)
                || ($this->deletePermission == 'both' && (in_array(user()->id, $taskUsers) || $service job->added_by == user()->id))
                || ($this->deletePermission == 'owned' && (in_array('customer', user_roles()) && $service job->site && ($service job->site->client_id == user()->id)))
                || ($this->deletePermission == 'both' && (in_array('customer', user_roles()) && ($service job->site && ($service job->site->client_id == user()->id)) || $service job->added_by == user()->id))
            )
        );

        $this->taskBoardStatus = TaskboardColumn::all();
        Service Job::where('recurring_task_id', $id)->delete();

        // Delete current service job
        $service job->delete();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['redirectUrl' => route('service jobs.index')]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.addTask');

        $this->addPermission = user()->permission('add_tasks');
        $this->projectShortCode = '';
        $this->site = request('task_project_id') ? Site::with('projectMembers')->findOrFail(request('task_project_id')) : null;

        if (is_null($this->site) || ($this->site->project_admin != user()->id)) {
            abort_403(!in_array($this->addPermission, ['all', 'added']));
        }

        $this->service job = (request()['duplicate_task']) ? Service Job::with('users', 'label', 'site')->findOrFail(request()['duplicate_task'])->withCustomFields() : null;
        $this->selectedLabel = TaskLabel::where('task_id', request()['duplicate_task'])->get()->pluck('label_id')->toArray();
        $this->projectMember = TaskUser::where('task_id', request()['duplicate_task'])->get()->pluck('user_id')->toArray();

        $this->sites = Site::allProjects(true);

        $this->taskLabels = TaskLabelList::whereNull('project_id')->get();
        $this->projectID = request()->task_project_id;

        if (request('task_project_id')) {
            $site = Site::findOrFail(request('task_project_id'));
            $this->projectShortCode = $site->project_short_code;
            $this->taskLabels = TaskLabelList::where('project_id', request('task_project_id'))->orWhere('project_id', null)->get();
            $this->milestones = ProjectMilestone::where('project_id', request('task_project_id'))->whereNot('status', 'complete')->get();
        }
        else {
            if ($this->service job && $this->service job->site) {
                $this->milestones = $this->service job->site->incompleteMilestones;
            }
            else {
                $this->milestones = collect([]);
            }
        }

        $this->columnId = request('column_id');
        $this->categories = TaskCategory::all();

        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();
        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();

        if (request()->has('default_assign') && request('default_assign') != '') {
            $this->defaultAssignee = request('default_assign');
        }

        $this->dependantTasks = $completedTaskColumn ? Service Job::where('board_column_id', '<>', $completedTaskColumn->id)
            ->where('project_id', $this->projectID)
            ->whereNotNull('due_date')->get() : [];

        $this->allTasks = $completedTaskColumn ? Service Job::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date')->get() : [];

        $viewEmployeePermission = user()->permission('view_employees');

        if (!is_null($this->site)) {
            if ($this->site->public) {
                $this->cleaners = User::allEmployees(null, true, ($viewEmployeePermission == 'all' ? 'all' : null));

            }
            else {

                $this->cleaners = $this->site->projectMembers;
            }
        }
        else if (!is_null($this->service job) && !is_null($this->service job->project_id)) {
            if ($this->service job->site->public) {
                $this->cleaners = User::allEmployees(null, true, ($viewEmployeePermission == 'all' ? 'all' : null));
            }
            else {

                $this->cleaners = $this->service job->site->projectMembers;
            }
        }
        else {
            if (in_array('customer', user_roles())) {
                $this->cleaners = collect([]); // Do not show all cleaners to customer

            }
            else {
                $this->cleaners = User::allEmployees(null, true, ($viewEmployeePermission == 'all' ? 'all' : null));
            }

        }

        $service job = new Service Job();

        $getCustomFieldGroupsWithFields = $service job->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'service jobs.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service jobs.create', $this->data);
    }

    // The function is called for duplicate code also
    public function store(StoreTask $request)
    {
        $site = request('project_id') ? Site::findOrFail(request('project_id')) : null;

        if (is_null($site) || ($site->project_admin != user()->id)) {
            $this->addPermission = user()->permission('add_tasks');
            abort_403(!in_array($this->addPermission, ['all', 'added']));
        }

        // Check if site has remaining time for time-based sites
        if ($site && $site->calculate_task_progress == 'project_total_time') {
            if (!$this->hasProjectRemainingTime($site->id)) {
                return Reply::error(__('team chat.projectTimeExceeded'));
            }
        }

        DB::beginTransaction();
        $ganttTaskArray = [];
        $gantTaskLinkArray = [];

        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();
        $service job = new Service Job();
        $service job->heading = $request->heading;
        $service job->description = trim_editor($request->description);
        $dueDate = ($request->has('without_duedate')) ? null : Carbon::createFromFormat(company()->date_format, $request->due_date);
        $service job->start_date = Carbon::createFromFormat(company()->date_format, $request->start_date);
        $service job->due_date = $dueDate;
        $service job->project_id = $request->project_id;
        $service job->task_category_id = $request->category_id;
        $service job->priority = $request->priority;
        $service job->board_column_id = $taskBoardColumn->id;

        if ($request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '') {
            $dependentTask = Service Job::findOrFail($request->dependent_task_id);

            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                /* @phpstan-ignore-line */
                return Reply::error(__('team chat.taskDependentDate'));
            }

            $service job->dependent_task_id = $request->dependent_task_id;
        }

        $service job->is_private = $request->has('is_private') ? 1 : 0;
        $service job->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $service job->estimate_hours = $request->estimate_hours;
        $service job->estimate_minutes = $request->estimate_minutes;

        if ($request->board_column_id) {
            $service job->board_column_id = $request->board_column_id;
        }

        $waitingApprovalTaskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        if($request->board_column_id == $waitingApprovalTaskBoardColumn->id){
            $service job->approval_send = 1;
        }else{
            $service job->approval_send = 0;
        }

        if ($request->milestone_id != '') {
            $service job->milestone_id = $request->milestone_id;
        }

        // Add repeated service job
        $service job->repeat = $request->repeat ? 1 : 0;

        if ($site) {
            $projectLastTaskCount = Service Job::projectTaskCount($site->id);

            if (isset($site->project_short_code)) {
                $service job->task_short_code = $site->project_short_code . '-' . $this->getTaskShortCode($site->project_short_code, $projectLastTaskCount);
            }
            else{
                $service job->task_short_code = $projectLastTaskCount + 1;
            }
        }

        $service job->save();

        // Save labels

        $service job->labels()->sync($request->task_labels);


        if (!is_null($request->taskId)) {

            $taskExists = TaskFile::where('task_id', $request->taskId)->get();

            if ($taskExists) {
                foreach ($taskExists as $taskExist) {
                    $file = new TaskFile();
                    $file->user_id = $taskExist->user_id;
                    $file->task_id = $service job->id;

                    $fileName = Files::generateNewFileName($taskExist->filename);

                    Files::copy(TaskFile::FILE_PATH . '/' . $taskExist->task_id . '/' . $taskExist->hashname, TaskFile::FILE_PATH . '/' . $service job->id . '/' . $fileName);

                    $file->filename = $taskExist->filename;
                    $file->hashname = $fileName;
                    $file->size = $taskExist->size;
                    $file->save();


                    $this->logTaskActivity($service job->id, $this->user->id, 'fileActivity', $service job->board_column_id);
                }
            }


            $subTask = SubTask::with(['files'])->where('task_id', $request->taskId)->get();


            if ($subTask) {
                foreach ($subTask as $subTasks) {
                    $subTaskData = new SubTask();
                    $subTaskData->title = $subTasks->title;
                    $subTaskData->task_id = $service job->id;
                    $subTaskData->description = trim_editor($subTasks->description);

                    if ($subTasks->start_date != '' && $subTasks->due_date != '') {
                        $subTaskData->start_date = $subTasks->start_date;
                        $subTaskData->due_date = $subTasks->due_date;
                    }

                    $subTaskData->assigned_to = $subTasks->assigned_to;

                    $subTaskData->save();

                    if ($subTasks->files) {
                        foreach ($subTasks->files as $fileData) {
                            $file = new SubTaskFile();
                            $file->user_id = $fileData->user_id;
                            $file->sub_task_id = $subTaskData->id;

                            $fileName = Files::generateNewFileName($fileData->filename);

                            Files::copy(SubTaskFile::FILE_PATH . '/' . $fileData->sub_task_id . '/' . $fileData->hashname, SubTaskFile::FILE_PATH . '/' . $subTaskData->id . '/' . $fileName);

                            $file->filename = $fileData->filename;
                            $file->hashname = $fileName;
                            $file->size = $fileData->size;
                            $file->save();
                        }
                    }
                }
            }
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $service job->updateCustomFieldData($request->custom_fields_data);
        }

        // For gantt chart
        if ($request->page_name && !is_null($service job->due_date) && $request->page_name == 'ganttChart') {
            $service job = Service Job::find($service job->id);
            $parentGanttId = $request->parent_gantt_id;

            /* @phpstan-ignore-next-line */

            $taskDuration = $service job->due_date->diffInDays($service job->start_date);
            /* @phpstan-ignore-line */
            $taskDuration = $taskDuration + 1;

            $ganttTaskArray[] = [
                'id' => $service job->id,
                'text' => $service job->heading,
                'start_date' => $service job->start_date->format('Y-m-d'), /* @phpstan-ignore-line */
                'duration' => $taskDuration,
                'parent' => $parentGanttId,
                'taskid' => $service job->id
            ];

            $gantTaskLinkArray[] = [
                'id' => 'link_' . $service job->id,
                'source' => $service job->dependent_task_id != '' ? $service job->dependent_task_id : $parentGanttId,
                'target' => $service job->id,
                'type' => $service job->dependent_task_id != '' ? 0 : 1
            ];
        }


        DB::commit();

        if (request()->add_more == 'true') {
            unset($request->project_id);
            $html = $this->create();

            return Reply::successWithData(__('team chat.recordSaved'), ['html' => $html, 'add_more' => true, 'taskID' => $service job->id]);
        }

        if ($request->page_name && $request->page_name == 'ganttChart') {

            return Reply::successWithData(
                'team chat.recordSaved',
                [
                    'service jobs' => $ganttTaskArray,
                    'links' => $gantTaskLinkArray
                ]
            );
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('service jobs.index');
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['redirectUrl' => $redirectUrl, 'taskID' => $service job->id]);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $editTaskPermission = user()->permission('edit_tasks');
        $this->service job = Service Job::with('users', 'label', 'site',)->findOrFail($id)->withCustomFields();
        $this->taskUsers = $taskUsers = $this->service job->users->pluck('id')->toArray();
        $this->type = request()->type;
        abort_403(
            !($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $this->service job->added_by == user()->id)
                || ($this->service job->site && ($this->service job->site->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $this->service job->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('customer', user_roles()) && $this->service job->site && ($this->service job->site->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('customer', user_roles()) && ($this->service job->site && ($this->service job->site->client_id == user()->id)) || $this->service job->added_by == user()->id))
            )
        );

        $getCustomFieldGroupsWithFields = $this->service job->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        $this->pageTitle = __('modules.service jobs.updateTask');
        $this->labelIds = $this->service job->label->pluck('label_id')->toArray();
        $this->sites = Site::allProjects(true);
        $this->categories = TaskCategory::all();
        $projectId = $this->service job->project_id;

        if($projectId){
            $this->taskLabels = TaskLabelList::where('project_id', $projectId)->orWhereNull('project_id')->get();
        }else{
            $this->taskLabels = TaskLabelList::whereNull('project_id')->get();
        }

        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();
        $this->changeStatusPermission = user()->permission('change_status');
        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();
        $this->waitingApprovalTaskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        if ($completedTaskColumn) {
            $this->allTasks = Service Job::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date')->where('id', '!=', $id)->where('project_id', $projectId)->get();
        }
        else {
            $this->allTasks = [];
        }

        if ($this->service job->project_id) {
            if ($this->service job->site->public) {
                $this->cleaners = User::allEmployees(null, false, ($editTaskPermission == 'all' ? 'all' : null));

            }
            else {
                $this->cleaners = $this->service job->site->projectMembersWithoutScope;
            }
        }
        else {
            if ($editTaskPermission == 'added' || $editTaskPermission == 'owned') {
                $this->cleaners = ((count($this->service job->users) > 0) ? $this->service job->users : User::allEmployees(null, true, ($editTaskPermission == 'all' ? 'all' : null)));

            }
            else {
                $this->cleaners = User::allEmployees(null, false, ($editTaskPermission == 'all' ? 'all' : null));
            }
        }


        $uniqueId = $this->service job->task_short_code;
        // check if unuqueId contains -
        if (strpos($uniqueId, '-') !== false) {
            $uniqueId = explode('-', $uniqueId, 2);
            $this->projectUniId = $uniqueId[0];
            $this->taskUniId = $uniqueId[1];
        }
        else {
            $this->projectUniId = ($this->service job->project_id != null) ? $this->service job->site->project_short_code : null;
            $this->taskUniId = $uniqueId;
        }

        $userId = $this->service job->users->pluck('id')->toArray();
        $startDate = $this->service job->start_date;
        $dueDate = $this->service job->due_date;
        $leaves = $this->leaves($userId, $startDate, $dueDate);

        if (!is_null($leaves)) {
            $data = [];

            foreach ($leaves as $key => $value) {
                $values = implode(', ', $value);
                $data[] = $key . __('modules.service jobs.leaveOn') . ' ' . $values;
            }

            $this->leaveData = implode("\n", $data);
            /* @phpstan-ignore-line */

        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'service jobs.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service jobs.create', $this->data);

    }

    public function update(UpdateTask $request, $id)
    {
        $service job = Service Job::with('users', 'label', 'site')->findOrFail($id)->withCustomFields();
        $editTaskPermission = user()->permission('edit_tasks');
        $taskUsers = $service job->users->pluck('id')->toArray();

        abort_403(
            !($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $service job->added_by == user()->id)
                || ($service job->site && ($service job->site->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $service job->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('customer', user_roles()) && $service job->site && ($service job->site->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('customer', user_roles()) && ($service job->site && ($service job->site->client_id == user()->id)) || $service job->added_by == user()->id))
            )
        );

        $dueDate = ($request->has('without_duedate')) ? null : Carbon::createFromFormat(company()->date_format, $request->due_date);
        $service job->heading = $request->heading;
        $service job->description = trim_editor($request->description);
        $service job->start_date = Carbon::createFromFormat(company()->date_format, $request->start_date);
        $service job->due_date = $dueDate;
        $service job->task_category_id = $request->category_id;
        $service job->priority = $request->priority;


        if ($request->has('board_column_id')) {

            $service job->board_column_id = $request->board_column_id;
            $service job->approval_send = 0;
            $taskBoardColumn = TaskboardColumn::findOrFail($request->board_column_id);

            if ($taskBoardColumn->slug == 'completed') {
                $service job->completed_on = now()->format('Y-m-d');
            }
            else {
                $service job->completed_on = null;
            }
        }

        if($request->select_value == 'Waiting Approval'){

            $taskBoardColumn = TaskboardColumn::where('column_name', $request->select_value)->where('company_id', company()->id)->first();
            $service job->board_column_id = $taskBoardColumn->id;
            $service job->approval_send = 1;
        }

        $service job->dependent_task_id = $request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '' ? $request->dependent_task_id : null;
        $service job->is_private = $request->has('is_private') ? 1 : 0;
        $service job->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $service job->estimate_hours = $request->estimate_hours;
        $service job->estimate_minutes = $request->estimate_minutes;

        if ($request->project_id != '') {
            $service job->project_id = $request->project_id;
            ProjectTimeLog::where('task_id', $id)->update(['project_id' => $request->project_id]);
        }
        else {
            $service job->project_id = null;
        }

        if ($request->has('milestone_id')) {
            $service job->milestone_id = $request->milestone_id;
        }

        if ($request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '') {
            $dependentTask = Service Job::findOrFail($request->dependent_task_id);

            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                return Reply::error(__('team chat.taskDependentDate'));
            }

            $service job->dependent_task_id = $request->dependent_task_id;
        }

        // Add repeated service job
        $service job->repeat = $request->repeat ? 1 : 0;

        if ($request->has('repeat')) {
            $service job->repeat_count = $request->repeat_count;
            $service job->repeat_type = $request->repeat_type;
            $service job->repeat_cycles = $request->repeat_cycles;
        }

        $service job->load('site');

        $site = $service job->site;

        if ($site && $service job->isDirty('project_id')) {
            $projectLastTaskCount = Service Job::projectTaskCount($site->id);
            $service job->task_short_code = $site->project_short_code . '-' . $this->getTaskShortCode($site->project_short_code, $projectLastTaskCount);
        }
        $service job->save();

        // save labels
        $service job->labels()->sync($request->task_labels);

        // To add custom fields data
        if ($request->custom_fields_data) {
            $service job->updateCustomFieldData($request->custom_fields_data);
        }

        // Sync service job users
        $service job->users()->sync($request->user_id);

        if(!empty($request->user_id)){
            $newlyAssignedUserIds = array_diff($request->user_id, $taskUsers);
            if (!empty($newlyAssignedUserIds)) {
                $newUsers = User::whereIn('id', $newlyAssignedUserIds)->get();
                event(new TaskEvent($service job, $newUsers, 'NewTask'));
            }
        }

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => route('service jobs.show', $id)]);
    }

    /**
     * @param $projectShortCode
     * @param $lastProjectCount
     * @return mixed
     */
    public function getTaskShortCode($projectShortCode, $lastProjectCount)
    {
        $service job = Service Job::where('task_short_code', $projectShortCode . '-' . $lastProjectCount)->exists();

        if ($service job) {
            return $this->getTaskShortCode($projectShortCode, $lastProjectCount + 1);
        }

        return $lastProjectCount;

    }

    public function show($id)
    {

        $viewTaskFilePermission = user()->permission('view_task_files');
        $viewSubTaskPermission = user()->permission('view_sub_tasks');
        $this->viewTaskCommentPermission = user()->permission('view_task_comments');
        $this->viewTaskNotePermission = user()->permission('view_task_notes');
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

        $this->service job = Service Job::with(
            ['boardColumn', 'site', 'users', 'label', 'approvedTimeLogs', 'mentionTask',
                'approvedTimeLogs.user', 'approvedTimeLogs.activeBreak', 'comments','activeUsers',
                'comments.commentEmoji', 'comments.like', 'comments.dislike', 'comments.likeUsers',
                'comments.dislikeUsers', 'comments.user', 'checklists.files', 'userActiveTimer', 'dependentTask',
                'files' => function ($q) use ($viewTaskFilePermission) {
                    if ($viewTaskFilePermission == 'added') {
                        $q->where('added_by', $this->userId);
                    }
                },
                'checklists' => function ($q) use ($viewSubTaskPermission) {
                    if ($viewSubTaskPermission == 'added') {
                        $q->where('added_by', $this->userId);
                    }
                }]
        )
            ->withCount('checklists', 'files', 'comments', 'activeTimerAll')
            ->findOrFail($id)->withCustomFields();


        $this->taskUsers = $taskUsers = $this->service job->users->pluck('id')->toArray();

        $taskuserData = [];

        $usersData = $this->service job->users;

        if ($this->service job->createBy && !in_array($this->service job->createBy->id, $taskUsers)) {
            $url = route('cleaners.show', [$this->service job->createBy->user_id ?? $this->service job->createBy->id]);
            $taskuserData[] = ['id' => $this->service job->createBy->user_id ?? $this->service job->createBy->id, 'value' => $this->service job->createBy->user->name ?? $this->service job->createBy->name, 'image' => $this->service job->createBy->user->image_url ?? $this->service job->createBy->image_url, 'link' => $url];
        }

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->user_id ?? $user->id]);
            $taskuserData[] = ['id' => $user->user_id ?? $user->id, 'value' => $user->user->name ?? $user->name, 'image' => $user->user->image_url ?? $user->image_url, 'link' => $url];

        }

        $this->taskuserData = $taskuserData;

        $this->taskSettings = TaskSetting::first();
        $viewTaskPermission = user()->permission('view_tasks');
        $mentionUser = $this->service job->mentionTask->pluck('user_id')->toArray();

        $overrideViewPermission = false;

        if (request()->has('tab') && request('tab') === 'site') {
            $overrideViewPermission = true;
        }

        abort_403(
            !(
                $overrideViewPermission == true
                || $viewTaskPermission == 'all'
                || ($viewTaskPermission == 'added' && $this->service job->added_by == $this->userId)
                || ($viewTaskPermission == 'owned' && in_array($this->userId, $taskUsers))
                || ($viewTaskPermission == 'both' && (in_array($this->userId, $taskUsers) || $this->service job->added_by == $this->userId))
                || ($viewTaskPermission == 'owned' && in_array('customer', user_roles()) && $this->service job->project_id && $this->service job->site->client_id == $this->userId)
                || ($viewTaskPermission == 'both' && in_array('customer', user_roles()) && $this->service job->project_id && $this->service job->site->client_id == $this->userId)
                || ($this->viewUnassignedTasksPermission == 'all' && in_array('cleaner', user_roles()))
                || ($this->service job->project_id && $this->service job->site->project_admin == $this->userId)
                || ((!is_null($this->service job->mentionTask)) && in_array($this->userId, $mentionUser))
            )

        );

        if (!$this->service job->project_id || ($this->service job->project_id && $this->service job->site->project_admin != $this->userId)) {

            abort_403($this->viewUnassignedTasksPermission == 'none' && count($taskUsers) == 0 && ((is_null($this->service job->mentionTask)) && in_array($userId, $mentionUser)));

        }

        if($this->service job->task_short_code){
            $this->pageTitle = __('app.service job') . ' # ' . $this->service job->task_short_code;
        }else{
            $this->pageTitle = __('app.service job');
        }
        $this->status = TaskboardColumn::where('id', $this->service job->board_column_id)->first();
        $getCustomFieldGroupsWithFields = $this->service job->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        $this->cleaners = User::join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('project_time_logs', 'project_time_logs.user_id', '=', 'users.id')
            ->leftJoin('roles', 'employee_details.designation_id', '=', 'roles.id');


        $this->cleaners = $this->cleaners->select(
            'users.name',
            'users.image',
            'users.id',
            'roles.name as designation_name'
        );

        $this->cleaners = $this->cleaners->where('project_time_logs.task_id', '=', $id);

        $this->cleaners = $this->cleaners->groupBy('project_time_logs.user_id')
            ->orderBy('users.name')
            ->get();

        $this->breakMinutes = ProjectTimeLogBreak::taskBreakMinutes($this->service job->id);

        // Add Gitlab service job details if available
        if (module_enabled('Gitlab')) {
            if (in_array('gitlab', user_modules()) && !is_null($this->service job->project_id)) {

                /** @phpstan-ignore-next-line */
                $this->gitlabSettings = \Modules\Gitlab\Entities\GitlabSetting::where('user_id', $this->userId)->first();

                if (!$this->gitlabSettings) {
                    /** @phpstan-ignore-next-line */
                    $this->gitlabSettings = \Modules\Gitlab\Entities\GitlabSetting::whereNull('user_id')->first();
                }

                if ($this->gitlabSettings) {
                    /** @phpstan-ignore-next-line */
                    Config::set('gitlab.connections.main.token', $this->gitlabSettings->personal_access_token);
                    /** @phpstan-ignore-next-line */
                    Config::set('gitlab.connections.main.url', $this->gitlabSettings->gitlab_url);

                    /** @phpstan-ignore-next-line */
                    $gitlabProject = \Modules\Gitlab\Entities\GitlabProject::where('project_id', $this->service job->project_id)->first();
                    /** @phpstan-ignore-next-line */
                    $gitlabTask = \Modules\Gitlab\Entities\GitlabTask::where('task_id', $id)->first();

                    if ($gitlabTask) {
                        /** @phpstan-ignore-next-line */
                        $gitlabIssue = \GrahamCampbell\GitLab\Facades\GitLab::issues()->all(intval($gitlabProject->gitlab_project_id), ['iids' => [intval($gitlabTask->gitlab_task_iid)]]);

                        if ($gitlabIssue) {
                            $this->gitlabIssue = $gitlabIssue[0];
                        }
                    }
                }
            }
        }

        $tab = request('view');

        switch ($tab) {
        case 'sub_task':
            $this->tab = 'service jobs.ajax.sub_tasks';
            break;
        case 'comments':
            abort_403($this->viewTaskCommentPermission == 'none');

            $this->tab = 'service jobs.ajax.comments';
            break;
        case 'notes':
            abort_403($this->viewTaskNotePermission == 'none');
            $this->tab = 'service jobs.ajax.notes';
            break;
        case 'history':
            $this->tab = 'service jobs.ajax.history';
            break;
        case 'time_logs':
            abort_403(!in_array('timelogs', user_modules()));
            $this->tab = 'service jobs.ajax.timelogs';
            break;
        default:
            if ($this->taskSettings->files == 'yes' && in_array('customer', user_roles())) {
                $this->tab = 'service jobs.ajax.files';
            }
            elseif ($this->taskSettings->sub_task == 'yes' && in_array('customer', user_roles())) {
                $this->tab = 'service jobs.ajax.sub_tasks';
            }
            elseif ($this->taskSettings->comments == 'yes' && in_array('customer', user_roles())) {
                abort_403($this->viewTaskCommentPermission == 'none');
                $this->tab = 'service jobs.ajax.comments';
            }
            elseif ($this->taskSettings->time_logs == 'yes' && in_array('customer', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'service jobs.ajax.timelogs';
            }
            elseif ($this->taskSettings->notes == 'yes' && in_array('customer', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'service jobs.ajax.notes';
            }
            elseif ($this->taskSettings->history == 'yes' && in_array('customer', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'service jobs.ajax.history';
            }
            elseif (!in_array('customer', user_roles())) {
                $this->tab = 'service jobs.ajax.files';
            }
            break;
        }

        if (request()->ajax()) {
            $view = request('json') ? $this->tab : 'service jobs.ajax.show';

            return $this->returnAjax($view);
        }


        $this->view = 'service jobs.ajax.show';

        return view('service jobs.create', $this->data);

    }

    public function storePin(Request $request)
    {
        $userId = UserService::getUserId();
        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->user_id = $userId;
        $pinned->save();

        return Reply::success(__('team chat.pinnedSuccess'));
    }

    public function destroyPin(Request $request, $id)
    {
        $userId = UserService::getUserId();
        $type = ($request->type == 'service job') ? 'task_id' : 'project_id';

        Pinned::where($type, $id)->where('user_id', $userId)->delete();

        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function checkTask($taskID)
    {
        $service job = Service Job::withTrashed()->findOrFail($taskID);
        $subTask = SubTask::where(['task_id' => $taskID, 'status' => 'incomplete'])->count();

        return Reply::dataOnly(['taskCount' => $subTask, 'lastStatus' => $service job->boardColumn->slug]);
    }

    public function sendApproval(Request $request){

        $service job = Service Job::findOrFail($request->taskId);
        $taskBoardColumn = TaskboardColumn::where('slug', 'waiting_approval')->first();

        $service job->approval_send = $request->isApproval ?? 0;
        $service job->board_column_id = $taskBoardColumn->id;
        $service job->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function waitingApproval(WaitingForApprovalDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_tasks');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->assignedTo = request()->assignedTo;

            if (request()->has('assignee') && request()->assignee == 'me') {
                $this->assignedTo = user()->id;
            }

            $this->sites = Site::allProjects();

            if (in_array('customer', user_roles())) {
                $this->customers = User::customer();
            }
            else {
                $this->customers = User::allClients();
            }

            $this->cleaners = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
            $this->milestones = ProjectMilestone::all();

            $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();

            $projectIds = Site::where('project_admin', user()->id)->pluck('id');

            if (!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && $projectIds->isEmpty())) {
                $user = User::findOrFail(user()->id);
                $this->waitingApprovalCount = $user->service jobs()->where('board_column_id', $taskBoardColumn->id)->count();
            }elseif(!in_array('admin', user_roles()) && (in_array('cleaner', user_roles()) && !$projectIds->isEmpty())) {
                $this->waitingApprovalCount = Service Job::whereIn('project_id', $projectIds)->where('board_column_id', $taskBoardColumn->id)->count();
            }else{
                $this->waitingApprovalCount = Service Job::where('board_column_id', $taskBoardColumn->id)->count();
            }
        }

        return $dataTable->render('service jobs.waiting-approval', $this->data);
    }

    public function statusReason(Request $request){

        $this->taskStatus = $request->taskStatus;
        $this->taskId = $request->taskId;
        $this->userId = $request->userId;

        return view('service jobs.status_reason_modal', $this->data);
    }

    public function storeStatusReason(ActionTask $request){

        $service job = Service Job::findOrFail($request->taskId);
        $taskBoardColumn = TaskboardColumn::where('slug', $request->taskStatus)->first();
        $service job->board_column_id = $taskBoardColumn->id;
        $service job->approval_send = 0;
        $service job->save();

        $comment = new TaskComment();
        $comment->comment = $request->reason;
        $comment->task_id = $request->taskId;
        $comment->user_id = user()->id;
        $comment->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function clientDetail(Request $request)
    {
        $site = Site::with('customer')->findOrFail($request->id);

        if (!is_null($site->customer)) {
            $data = '<h5 class= "mb-2 f-13"> ' . __('modules.sites.projectClient') . '</h5>';
            $data .= view('components.customer', ['user' => $site->customer]);
            /* @phpstan-ignore-line */
        }
        else {
            $data = '<p> ' . __('modules.sites.projectDoNotHaveClient') . '</p>';
        }

        return Reply::dataOnly(['data' => $data]);
    }

    public function updateTaskDuration(Request $request, $id)
    {
        $service job = Service Job::findOrFail($id);
        $service job->start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
        $service job->due_date = (!is_null($service job->due_date)) ? Carbon::createFromFormat('d/m/Y', $request->end_date)->addDay()->format('Y-m-d') : null;
        $service job->save();

        return Reply::success('team chat.updateSuccess');
    }

    public function projectTasks($id)
    {
        if (request()->has('for_timelogs')) {
            $service jobs = Service Job::projectLogTimeTasks($id);
            $options = BaseModel::options($service jobs, null, 'heading');

            return Reply::dataOnly(['status' => 'success', 'data' => $options]);
        }

        $options = '<option value="">--</option>';

        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();

        $service jobs = Service Job::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date');

        if ($id != 0 && $id != '') {
            $service jobs = $service jobs->where('project_id', $id);
        }

        $service jobs = $completedTaskColumn ? $service jobs->get() : [];

        foreach ($service jobs as $item) {

            $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'></div>  ' . $item->heading . ' ( Due date: ' . $item->due_date->format(company()->date_format) . ' ) " value="' . $item->id . '"> ' . $item->heading . '  ' . $item->due_date . ' </option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function members($id)
    {
        $options = '<option value="">--</option>';
        $startDate = null;
        $startDateMin = null;

        if ($id != 0) {
            $members = Service Job::with('users')->findOrFail($id);

            foreach ($members->users as $item) {
                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';
                if($item->status == 'active'){
                    $content = ( $item->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                    $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '' . $self_select . '' . $content . '" value="' . $item->id .'"> ' . $item->name . ' </option>';
                }
            }

            $startDateMin = $members->start_date ? $members->start_date->format('Y-m-d') : null;
            $startDate = $members->start_date && $members->start_date->lt(now()) ? now()->format('Y-m-d') : ($members->start_date ? $members->start_date->format('Y-m-d') : null);
            
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'startDate' => $startDate, 'startDateMin' => $startDateMin]);
    }

    public function reminder()
    {
        $taskID = request()->id;
        $service job = Service Job::with('users')->findOrFail($taskID);

        // Send  reminder notification to user
        event(new TaskReminderEvent($service job));

        return Reply::success('team chat.reminderMailSuccess');
    }

    public function checkLeaves()
    {
        $startDate = request()->start_date ? companyToYmd(request()->start_date) : null;
        $dueDate = request()->due_date ? companyToYmd(request()->due_date) : null;

        if (request()->start_date && request()->due_date && request()->user_id) {
            $data = $this->leaves(request()->user_id, $startDate, $dueDate);

            return reply::dataOnly(['data' => $data]);
        }
    }

    public function leaves($userIds, $startDate, $dueDate)
    {
        $leaveDates = [];

        foreach ($userIds as $userId) {
            $leaves = Leave::with('user')
                ->where('user_id', $userId)
                ->whereBetween('leave_date', [$startDate, $dueDate])
                ->get();

            foreach ($leaves as $leave) {
                $userName[] = $leave->user->name;
                $leaveDates[] = $leave->leave_date->format('d,M Y');
            }
        }

        if (isset($userName)) {
            $uniqueUser = array_unique($userName);
            $data = [];

            foreach ($uniqueUser as $name) {
                $data[$name] = [];

                foreach ($userName as $key => $value) {
                    if ($value == $name) {
                        $data[$name][] = $leaveDates[$key];
                        /** @phpstan-ignore-line */
                    }
                }
            }

            return $data;
        }
    }

}
