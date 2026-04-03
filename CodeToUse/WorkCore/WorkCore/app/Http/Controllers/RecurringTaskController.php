<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Service Job;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Site;
use App\Models\SubTask;
use App\Models\TaskFile;
use App\Models\TaskUser;
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
use App\DataTables\RecurringTasksDataTable;
use App\DataTables\TasksDataTable;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Service Jobs\StoreTask;
use App\Http\Requests\Service Jobs\UpdateTask;
use App\Events\TaskEvent;
use App\Helper\UserService;
use App\Models\ClientContact;

class RecurringTaskController extends AccountBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskRecurring';
        $this->middleware(
            function ($request, $next) {
                abort_403(!in_array('service jobs', $this->user->modules));

                return $next($request);
            }
        );
    }

    public function index(RecurringTasksDataTable $dataTable)
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

        return $dataTable->render('recurring-service job.index', $this->data);
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
            $result = $this->deleteRecords($request);

            if ($result == true) {

                return response()->json([
                    'status' => 'success',
                    'message' => __('team chat.deleteSuccess'),
                    'redirectUrl' => route('recurring-service job.index')
                ]);
            }

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
        $service jobs = Service Job::whereIn('id', $ids)->get();

        // Check if any recurring_task_id of these service jobs also exists in the selected IDs
        $hasRecurringParentIncluded = $service jobs->contains(function ($service job) use ($ids) {
            return $service job->recurring_task_id && in_array($service job->recurring_task_id, $ids);
        });
        Service Job::whereIn('id', $ids)->delete();

        return $hasRecurringParentIncluded;
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

    public function destroy(Request $request, $id, RecurringTasksDataTable $dataTable)
    {
        $service job = Service Job::with('site')->findOrFail($id);

        $this->deletePermission = user()->permission('delete_tasks');

        $taskUsers = $service job->users->pluck('id')->toArray();
        $redirectUrl = false;

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

        Service Job::where('recurring_task_id', $id)->delete();

        // Delete current service job
        $service job->delete();

        $remainingTask = Service Job::where('id', $id)->orWhere('recurring_task_id', $id)->count();

        if($remainingTask == 0){

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

            if($service job->recurring_task_id == null){
                $redirectUrl = true;
            }

            if($redirectUrl == true){
                return Reply::successWithData(__('team chat.deleteSuccess'), ['redirectUrl' => route('recurring-service job.index')]);
            }
            return Reply::success(__('team chat.deleteSuccess'));
        }

        return Reply::success(__('team chat.deleteSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.add') . ' ' . __('app.menu.taskRecurring');

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

        if (!is_null($this->site)) {
            if ($this->site->public) {
                $this->cleaners = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));

            }
            else {

                $this->cleaners = $this->site->projectMembers;
            }
        }
        else if (!is_null($this->service job) && !is_null($this->service job->project_id)) {
            if ($this->service job->site->public) {
                $this->cleaners = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
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
                $this->cleaners = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
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

        $this->view = 'recurring-service job.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('recurring-service job.create', $this->data);
    }

    // The function is called for duplicate code also
    public function store(StoreTask $request)
    {
        $site = request('project_id') ? Site::findOrFail(request('project_id')) : null;

        if (is_null($site) || ($site->project_admin != user()->id)) {
            $this->addPermission = user()->permission('add_tasks');
            abort_403(!in_array($this->addPermission, ['all', 'added']));
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

        if ($request->has('repeat')) {
            $service job->repeat_count = $request->repeat_count;
            $service job->repeat_type = $request->repeat_type;
            $service job->repeat_cycles = $request->repeat_cycles;
        }

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
            $redirectUrl = route('recurring-service job.index');
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['redirectUrl' => $redirectUrl, 'taskID' => $service job->id]);

    }

    public function show($id)
    {

        $this->viewPermission = user()->permission('view_tasks');
        $viewTaskFilePermission = user()->permission('view_task_files');
        $viewSubTaskPermission = user()->permission('view_sub_tasks');
        $this->viewTaskCommentPermission = user()->permission('view_task_comments');
        $this->viewTaskNotePermission = user()->permission('view_task_notes');
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
        $this->viewProjectPermission = user()->permission('view_projects');
        $this->taskSettings = TaskSetting::first();

        $this->service job = Service Job::with(
            ['boardColumn', 'site', 'users', 'label', 'approvedTimeLogs', 'mentionTask',
                'approvedTimeLogs.user', 'approvedTimeLogs.activeBreak', 'comments','activeUsers',
                'comments.commentEmoji', 'comments.like', 'comments.dislike', 'comments.likeUsers',
                'comments.dislikeUsers', 'comments.user', 'checklists.files', 'userActiveTimer',
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

        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

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

        $viewTaskPermission = user()->permission('view_tasks');
        $mentionUser = $this->service job->mentionTask->pluck('user_id')->toArray();

        $this->completedTaskCount = Service Job::where('recurring_task_id', $id)->where('status', 'completed')->count();

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

            abort_403($this->viewUnassignedTasksPermission == 'none' && count($taskUsers) == 0 && ((is_null($this->service job->mentionTask)) && in_array($this->userId, $mentionUser)));

        }

        $this->pageTitle = __('app.service job') . ' ' . __('app.details');
        $tab = request('tab');

        switch ($tab) {
        case 'service job':
                return $this->service jobs($id);
        default:
            $this->view = 'recurring-service job.ajax.show';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('recurring-service job.show', $this->data);
    }

    public function service jobs($recurringID)
    {
        $dataTable = new TasksDataTable();
        $viewPermission = user()->permission('view_tasks');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->recurringID = $recurringID;
        $this->service job = Service Job::findOrFail($recurringID);
        $this->recurringTasks = Service Job::where('id', $recurringID)->orWhere('recurring_task_id', $recurringID)->get();
        $this->taskBoardStatus = TaskboardColumn::all();

        $this->currentYear = now()->format('Y');
        $this->currentMonth = now()->month;

        /* year range from last 5 year to next year */
        $years = [];

        $latestFifthYear = (int)now()->subYears(5)->format('Y');
        $nextYear = (int)now()->addYear()->format('Y');

        for ($i = $latestFifthYear; $i <= $nextYear; $i++) {
            $years[] = $i;
        }

        $this->years = $years;

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'recurring-service job.ajax.service job';

        return $dataTable->render('recurring-service job.show', $this->data);
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

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => route('recurring-service job.show', $id)]);
    }

    public function getTaskShortCode($projectShortCode, $lastProjectCount)
    {
        $service job = Service Job::where('task_short_code', $projectShortCode . '-' . $lastProjectCount)->exists();

        if ($service job) {
            return $this->getTaskShortCode($projectShortCode, $lastProjectCount + 1);
        }

        return $lastProjectCount;

    }

}
