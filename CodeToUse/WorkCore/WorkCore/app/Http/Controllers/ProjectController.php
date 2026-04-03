<?php

namespace App\Http\Controllers;

use App\DataTables\ArchiveProjectsDataTable;
use App\DataTables\ArchiveTasksDataTable;
use App\DataTables\DiscussionDataTable;
use App\DataTables\ExpensesDataTable;
use App\DataTables\EstimatesDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\OrdersDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectNotesDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TasksDataTable;
use App\DataTables\TicketDataTable;
use App\DataTables\TimeLogsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Cleaner\ImportProcessRequest;
use App\Http\Requests\Admin\Cleaner\ImportRequest;
use App\Http\Requests\Site\StoreProject;
use App\Http\Requests\Site\UpdateProject;
use App\Imports\ProjectImport;
use App\Jobs\ImportProjectJob;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\DiscussionCategory;
use App\Models\Expense;
use App\Models\GanttLink;
use App\Models\Invoice;
use App\Models\MessageSetting;
use App\Models\Payment;
use App\Models\Pinned;
use App\Models\Site;
use App\Models\ProjectActivity;
use App\Models\ProjectCategory;
use App\Models\ProjectDepartment;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Models\ProjectMilestone;
use App\Models\ProjectNote;
use App\Models\ProjectStatusSetting;
use App\Models\Company;
use App\Models\ProjectTemplate;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\SubTask;
use App\Models\SubTaskFile;
use App\Models\Service Job;
use App\Models\TaskUser;
use App\Models\TaskboardColumn;
use App\Models\ProjectLabelList;
use App\Models\ProjectLabel;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\ImportExcel;
use App\Traits\ProjectProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonInterval;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Helper\UserService;
class ProjectController extends AccountBaseController
{

    use ProjectProgress, ImportExcel;

    private $onlyTrashedRecords = true;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.sites';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('sites', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403((!in_array($viewPermission, ['all', 'added', 'owned', 'both'])));

        if (!request()->ajax()) {

            if (in_array('customer', user_roles())) {
                $this->customers = User::customer();
            }
            else {
                $this->customers = User::allClients();
                $this->allEmployees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            }

            $this->categories = ProjectCategory::all();
            $this->zones = Team::all();
            $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();
            $this->projectLabels = ProjectLabelList::all();
        }

        return $dataTable->render('sites.index', $this->data);

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
        case 'archive':
            $this->archiveRecords($request);

            return Reply::success(__('team chat.projectArchiveSuccessfully'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('team chat.updateSuccess'));
        default:
            return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_projects') != 'all');

        Site::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        $items = explode(',', $request->row_ids);

        foreach ($items as $item) {
            // Delete site files
            Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $item);
        }
    }

    protected function archiveRecords($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Site::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function archiveDestroy($id)
    {
        Site::destroy($id);

        return Reply::success(__('team chat.projectArchiveSuccessfully'));
    }

    protected function changeStatus($request)
    {
        // bulk status change
        abort_403(user()->permission('edit_projects') != 'all');

        $projectIds = explode(',', $request->row_ids);
        $newStatus = $request->status;
        $sites = Site::whereIn('id', $projectIds)->get();

        foreach ($sites as $site) {

            if ($newStatus !== 'finished') {
                $this->handleNonFinishedStatus($site, $site->id, $newStatus);
            } else {
                $this->handleFinishedStatus($site, $site->id);
            }
        }

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function updateStatus(Request $request, $id)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        $site = Site::findOrFail($id);
        $newStatus = $request->status;

        if ($newStatus !== 'finished') {
            $this->handleNonFinishedStatus($site, $id, $newStatus);
        } else {
            $response = $this->handleFinishedStatus($site, $id);

            if (!$response) {
                return Reply::error(__('team chat.projectTasksNotCompleted'));
            }
        }

        return Reply::success(__('team chat.updateSuccess'));
    }

    private function handleNonFinishedStatus($site, $id, $newStatus)
    {
        if ($site->status == 'finished') {
            $site->completion_percent = $this->calculateProjectProgress($id, 'true');
        }

        $site->update(['status' => $newStatus]);
    }

    private function handleFinishedStatus($site, $id)
    {
        if ($site->calculate_task_progress === 'true') {
            // Site completion is based on service job progress
            if ($site->completion_percent < 100) {
                return false;
            }
        } else {
            // If service job progress is NOT being used, set percent to 100 if not already
            if ($site->completion_percent < 100) {
                $site->completion_percent = 100;
            }
        }

        $site->status = 'finished';
        $site->save();

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $site = Site::withTrashed()->findOrFail($id);
        $this->deletePermission = user()->permission('delete_projects');
        $userId = UserService::getUserId();
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $site->added_by == $userId)));

        // Delete site files
        Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $id);

        Invoice::where('project_id', $id)->update(['project_id' => null]);
        Payment::where('project_id', $id)->update(['project_id' => null]);
        
        $site->forceDelete();

        return Reply::success(__('team chat.deleteSuccess'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addProject');
        $this->customers = User::allClients(null, false, ($this->addPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->projectLabels = ProjectLabelList::all();
        $this->cleaners = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
        $this->redirectUrl = request()->redirectUrl;
        $userId = UserService::getUserId();

        $this->site = (request()['duplicate_project']) ? Site::with('customer', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.role', 'milestones', 'milestones.currency')->withTrashed()->findOrFail(request()['duplicate_project'])->withCustomFields() : null;

        if ($this->site) {
            $this->projectMembers = $this->site->members ? $this->site->members->pluck('user_id')->toArray() : null;
        }

        $this->projectTemplate = request('template') ? ProjectTemplate::with('projectMembers')->findOrFail(request('template')) : null;

        if ($this->projectTemplate) {
            $templateMembers = ProjectTemplate::findOrFail(request('template'));
            $this->projectTemplateMembers = $templateMembers->members ? $templateMembers->members->pluck('user_id')->toArray() : null;
            // do not remove below commented line...
            // $this->projectTemplateMembers = $this->projectTemplate->projectMembers ? $this->projectTemplate->projectMembers->pluck('id')->toArray() : null;
        }

        $site = new Site();

        $getCustomFieldGroupsWithFields = $site->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        if (in_array('customer', user_roles())) {
            $this->customer = User::withoutGlobalScope(ActiveScope::class)->findOrFail($userId);

        }
        else {
            $this->customer = isset(request()->default_client) ? User::withoutGlobalScope(ActiveScope::class)->findOrFail(request()->default_client) : null;
        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'sites.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('sites.create', $this->data);

    }

    /**
     * @param StoreProject $request
     * @return array|mixed
     * @throws \Throwable
     */
    public function store(StoreProject $request)
    {

        $this->addPermission = user()->permission('add_projects');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        DB::beginTransaction();

        try {

            $startDate = companyToYmd($request->start_date);
            $deadline = !$request->has('without_deadline') ? companyToYmd($request->deadline) : null;

            $site = new Site();
            $site->project_name = $request->project_name;
            $site->project_short_code = $request->project_code;
            $site->start_date = $startDate;
            $site->deadline = $deadline;
            $site->client_id = $request->client_id;
            $site->public_gantt_chart = $request->public_gantt_chart ?? 'enable';
            $site->public_taskboard = $request->public_taskboard ?? 'enable';
            $site->need_approval_by_admin = $request->need_approval_by_admin ?? '0';

            if (!is_null($request->duplicateProjectID)) {

                $duplicateProject = Site::findOrFail($request->duplicateProjectID);

                $site->project_summary = trim_editor($duplicateProject->project_summary);
                $site->category_id = $duplicateProject->category_id;

                $site->client_view_task = $duplicateProject->client_view_task;
                $site->allow_client_notification = $duplicateProject->allow_client_notification;
                $site->manual_timelog = $duplicateProject->manual_timelog;
                $site->team_id = $duplicateProject->team_id;
                $site->status = 'not started';
                $site->project_budget = $duplicateProject->project_budget;
                $site->currency_id = $duplicateProject->currency_id;
                $site->hours_allocated = $duplicateProject->hours_allocated;
                $site->calculate_task_progress = $duplicateProject->calculate_task_progress;
                $site->notes = trim_editor($duplicateProject->notes);

            } else {
                $site->project_summary = trim_editor($request->project_summary);

                if ($request->category_id != '') {
                    $site->category_id = $request->category_id;
                }

                $site->client_view_task = $request->client_view_task ? 'enable' : 'disable';
                $site->allow_client_notification = $request->client_task_notification ? 'enable' : 'disable';
                $site->manual_timelog = $request->manual_timelog ? 'enable' : 'disable';


                $site->project_budget = $request->project_budget;
                $site->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
                $site->hours_allocated = $request->hours_allocated;

                $defaultsStatus = ProjectStatusSetting::where('default_status', 1)->get();

                foreach ($defaultsStatus as $default) {
                    $site->status = $default->status_name;
                }

                $site->miro_board_id = $request->miro_board_id;
                $site->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
                $site->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;
                $site->calculate_task_progress = $request->calculate_task_progress;
                $site->notes = trim_editor($request->notes);

            }

            if ($request->public) {
                $site->public = $request->public ? 1 : 0;
            }

            $site->save();
            $site->labels()->sync($request->project_labels);

            if ($request->calculate_task_progress == 'project_deadline') {
                // info('project_total_time1111111111');
                $site->calculate_task_progress = 'project_deadline';
                $site->completion_percent = $this->calculateProjectProgressByDeadline($site->id);
                
                if($site->completion_percent >= 100){
                    $site->status = 'finished';
                }else if($site->completion_percent < 100 && $request->status == 'finished'){
                    $site->status = 'in progress';
                }else{
                    $site->status = $request->status;
                }
            }
    


            if ($request->has('team_id') && is_array($request->team_id) && count($request->team_id) > 0) {
                foreach ($request->team_id as $team) {
                    ProjectDepartment::create([
                        'project_id' => $site->id,
                        'team_id' => $team
                    ]);
                }
            }

            if (trim_editor($request->notes) != '') {
                $site->notes()->create([
                    'title' => 'Note',
                    'details' => $request->notes,
                    'client_id' => $request->client_id,
                ]);
            }

            $this->logSearchEntry($site->id, $site->project_name, 'sites.show', 'site');
            $this->logProjectActivity($site->id, 'team chat.addedAsNewProject');

            if ($request->template_id) {
                $template = ProjectTemplate::with('projectMembers')->findOrFail($request->template_id);
                $counter = 1;

                $milestoneArray = [];

                foreach ($template->milestones as $milestone) {

                    $projectMilestone = new ProjectMilestone();
                    $projectMilestone->project_id = $site->id;
                    $projectMilestone->milestone_title = $milestone->milestone_title;
                    $projectMilestone->summary = $milestone->summary;
                    $projectMilestone->cost = $milestone->cost;
                    $projectMilestone->currency_id = $milestone->currency_id;
                    $projectMilestone->status = $milestone->status;
                    $projectMilestone->add_to_budget = 'no';
                    $projectMilestone->start_date = $milestone->start_date;
                    $projectMilestone->end_date = $milestone->end_date;
                    $projectMilestone->save();

                    $milestoneArray[$milestone->id] = $projectMilestone->id;

                }


                foreach ($template->service jobs as $service job) {

                    $projectTask = new Service Job();
                    $projectTask->project_id = $site->id;
                    $projectTask->heading = $service job->heading;
                    $projectTask->milestone_id = $milestoneArray[$service job->milestone_id] ?? null;
                    $projectTask->task_category_id = $service job->project_template_task_category_id;
                    $projectTask->description = trim_editor($service job->description);
                    $projectTask->start_date = $startDate;
                    $projectTask->due_date = $deadline;
                    $projectTask->priority = $service job->priority;

                    if(isset($site->project_short_code)){
                        $projectTask->task_short_code = $site->project_short_code . '-' . $counter;
                    }else{
                        $projectTask->task_short_code = $counter;
                    }
                    $projectTask->is_private = 0;
                    $projectTask->save();

                    $taskLabels = explode(",", $service job->task_labels);

                    if (!empty($taskLabels) && count($taskLabels) > 0 && !in_array('', $taskLabels)) {

                        $projectTask->labels()->sync($taskLabels);
                    }

                    foreach ($service job->usersMany as $value) {
                        TaskUser::create(
                            [
                                'user_id' => $value->id,
                                'task_id' => $projectTask->id
                            ]
                        );
                    }

                    foreach ($service job->checklists as $value) {
                        $projectTask->checklists()->create(['title' => $value->title]);
                    }

                    $counter++;
                }
            }

            if (!is_null($request->duplicateProjectID)) {
                $this->storeDuplicateProject($request, $site);
            }

            // To add custom fields data
            if ($request->custom_fields_data) {
                $site->updateCustomFieldData($request->custom_fields_data);
            }

            // Commit Transaction
            DB::commit();

            if($request->has('type') && $request->type == 'duplicateProject'){
                return Reply::success(__('team chat.projectCopiedSuccessfully'));
            }
            else {

                $redirectUrl = urldecode($request->redirect_url);

                if ($redirectUrl == '') {
                    $redirectUrl = route('sites.index');
                }

                return Reply::dataOnly(['projectID' => $site->id, 'redirectUrl' => $redirectUrl]);
            }

        } catch (TransportException $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Please configure SMTP details to add site. Visit Settings -> notification setting to set smtp ' . $e->getMessage(), 'smtp_error');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->site = Site::with('customer', 'label', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.role', 'milestones', 'milestones.currency', 'zones')
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();
        $userId = UserService::getUserId();
        $memberIds = $this->site->members->pluck('user_id')->toArray();

        $this->editPermission = user()->permission('edit_projects');
        $this->editProjectMembersPermission = user()->permission('edit_project_members');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $userId == $this->site->added_by)
            || ($this->editPermission == 'owned' && $userId == $this->site->client_id && in_array('customer', user_roles()))
            || ($this->editPermission == 'owned' && in_array($userId, $memberIds) && in_array('cleaner', user_roles()))
            || ($this->editPermission == 'both' && ($userId == $this->site->client_id || $userId == $this->site->added_by))
            || ($this->editPermission == 'both' && in_array($userId, $memberIds) && in_array('cleaner', user_roles()))
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.site');

        $getCustomFieldGroupsWithFields = $this->site->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->customers = User::allClients(null, false, ($this->editPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->projectLabels = ProjectLabelList::all();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();
        $this->departmentIds = $this->site->zones->pluck('team_id')->toArray();


        /**
         * If the site has zones, it retrieves the team IDs associated with those zones and fetches the users belonging to each team.
         * If the site does not have any zones, its giving all the cleaners.
         */
        if ($this->site->zones->count() > 0 && ($this->editPermission == 'all' || $this->editProjectMembersPermission == 'all')) {
            $this->teamIds = $this->site->zones->pluck('team_id')->toArray();
            $this->cleaners = collect([]);

            foreach ($this->teamIds as $teamId) {
                $team = User::departmentUsers($teamId);
                $this->cleaners = $this->cleaners->merge($team);
            }

        }
        else{
            $this->cleaners = '';

            if (($this->editPermission == 'all' || $this->editPermission = 'owned') || $this->editProjectMembersPermission == 'all') {
                $this->cleaners = User::allEmployees(null, false, ($this->editProjectMembersPermission == 'all' ? 'all' : null));
            }
        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'sites.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        abort_403(user()->permission('edit_projects') == 'added' && $this->site->added_by != $userId);

        return view('sites.create', $this->data);

    }

    /**
     * @param UpdateProject $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProject $request, $id)
    {
        $site = Site::findOrFail($id);
        $site->project_name = $request->project_name;
        $site->project_short_code = $request->project_code;

        $site->project_summary = trim_editor($request->project_summary);

        $site->start_date = companyToYmd($request->start_date);

        if (!$request->has('without_deadline')) {
            $site->deadline = companyToYmd($request->deadline);
        }
        else {
            $site->deadline = null;
        }

        if ($request->notes != '') {
            $site->notes = trim_editor($request->notes);
        }

        if ($request->category_id != '') {
            $site->category_id = $request->category_id;
        }

        if ($request->client_view_task) {
            $site->client_view_task = 'enable';
        }
        else {
            $site->client_view_task = 'disable';
        }

        if ($request->client_task_notification) {
            $site->allow_client_notification = 'enable';
        }
        else {
            $site->allow_client_notification = 'disable';
        }

        if ($request->manual_timelog) {
            $site->manual_timelog = 'enable';
        }
        else {
            $site->manual_timelog = 'disable';
        }

        $site->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;

        if ($request->calculate_task_progress == 'task_completion') {

            $site->calculate_task_progress = 'task_completion';
            $site->completion_percent = $this->calculateProjectProgress($id, 'true');

            if($site->completion_percent == 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }
        elseif ($request->calculate_task_progress == 'project_total_time') {
            $site->calculate_task_progress = 'project_total_time';
            $site->completion_percent = $this->calculateProjectProgressByTime($id);
            
            if($site->completion_percent >= 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }

        elseif ($request->calculate_task_progress == 'project_deadline') {
            $site->calculate_task_progress = 'project_deadline';
            $site->completion_percent = $this->calculateProjectProgressByDeadline($id);
            
            if($site->completion_percent >= 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }
        else {
            $site->calculate_task_progress = 'manual';
            $site->completion_percent = $request->completion_percent;
            
            if($request->completion_percent == 100){
                $site->status = 'finished';
            }else{
                $site->status = $request->status;
            }
        }

        $site->project_budget = $request->project_budget;
        $site->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
        $site->hours_allocated = $request->hours_allocated;
        $site->miro_board_id = $request->miro_board_id;

        // Recalculate progress if hours_allocated changed and site uses time-based calculation
        if ($site->calculate_task_progress == 'project_total_time' && $site->isDirty('hours_allocated')) {
            $site->completion_percent = $this->calculateProjectProgressByTime($id);
        }

        // Recalculate progress if deadline changed and site uses deadline-based calculation
        if ($site->calculate_task_progress == 'project_deadline' && $site->isDirty('deadline')) {
            $site->completion_percent = $this->calculateProjectProgressByDeadline($id);
        }

        if ($request->has('miroboard_checkbox')) {
            $site->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
        }
        else {
            $site->client_access = 0;
        }

        $site->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;

        if ($request->public) {
            $site->public = 1;
        }

        if ($request->private) {
            $site->public = 0;
        }


        if (!$request->private && !$request->public && $request->member_id) {
            $site->projectMembers()->sync($request->member_id);
        }

        if (is_null($request->member_id && $request->has('member_id'))) {
            $site->projectMembers()->detach();
        }

        $site->public_gantt_chart = $request->public_gantt_chart;
        $site->public_taskboard = $request->public_taskboard;
        $site->need_approval_by_admin = $request->need_approval_by_admin;
        $site->save();

        info($request->calculate_task_progress);
        info('sadfsdaf');
        if ($request->calculate_task_progress == 'task_completion') {
            info('task_completion');
            $site->calculate_task_progress = 'task_completion';
            $site->completion_percent = $this->calculateProjectProgress($id, 'true');
            info('completion_percent');
            info($site->completion_percent);
            if($site->completion_percent == 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }
        elseif ($request->calculate_task_progress == 'project_total_time') {
            $site->calculate_task_progress = 'project_total_time';
            $site->completion_percent = $this->calculateProjectProgressByTime($id);
            
            if($site->completion_percent >= 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }elseif ($request->calculate_task_progress == 'project_deadline') {
            $site->calculate_task_progress = 'project_deadline';
            $site->completion_percent = $this->calculateProjectProgressByDeadline($id);
            
            if($site->completion_percent >= 100){
                $site->status = 'finished';
            }else if($site->completion_percent < 100 && $request->status == 'finished'){
                $site->status = 'in progress';
            }else{
                $site->status = $request->status;
            }
        }

        // Recalculate progress if hours_allocated changed and site uses time-based calculation
        if ($site->calculate_task_progress == 'project_total_time' && $site->isDirty('hours_allocated')) {
            $site->completion_percent = $this->calculateProjectProgressByTime($id);
        }

        // Recalculate progress if deadline changed and site uses deadline-based calculation
        if ($site->calculate_task_progress == 'project_deadline' && $site->isDirty('deadline')) {
            $site->completion_percent = $this->calculateProjectProgressByDeadline($id);
        }

        $site->save();

        $site->labels()->sync($request->project_labels);

        if ($request->has('team_id')) {
            $site->projectDepartments()->sync($request->team_id);
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $site->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logProjectActivity($site->id, 'team chat.updateSuccess');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('sites.index');
        }

        return Reply::successWithData(__('team chat.updateSuccess'), ['projectID' => $site->id, 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $this->viewPermission = user()->permission('view_projects');
        $viewFilePermission = user()->permission('view_project_files');
        $this->viewMiroboardPermission = user()->permission('view_miroboard');
        $viewMilestonePermission = user()->permission('view_project_milestones');
        $this->viewPaymentPermission = user()->permission('view_project_payments');
        $this->viewProjectTimelogPermission = user()->permission('view_project_timelogs');
        $this->viewExpensePermission = user()->permission('view_project_expenses');
        $this->viewRatingPermission = user()->permission('view_project_rating');
        $this->viewBurndownChartPermission = user()->permission('view_project_burndown_chart');
        $this->viewProjectMemberPermission = user()->permission('view_project_members');
        $this->userId = UserService::getUserId();
        $this->site = Site::with(['customer', 'members', 'members.user','mentionProject', 'members.user.session', 'members.user.employeeDetail.role', 'milestones' => function ($q) use ($viewMilestonePermission) {
            if ($viewMilestonePermission == 'added') {
                $q->where('added_by', $this->userId);
            }
        },
            'milestones.currency', 'files' => function ($q) use ($viewFilePermission) {
                if ($viewFilePermission == 'added') {
                    $q->where('added_by', $this->userId);
                }
            }])
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $this->projectStatusColor = ProjectStatusSetting::where('status_name', $this->site->status)->first();
        $memberIds = $this->site->members->pluck('user_id')->toArray();
        $mentionIds = $this->site->mentionProject->pluck('user_id')->toArray();

        abort_403(!(
            $this->viewPermission == 'all'
            || $this->site->public
            || ($this->viewPermission == 'added' && $this->userId == $this->site->added_by)
            || ($this->viewPermission == 'owned' && $this->userId == $this->site->client_id && in_array('customer', user_roles()))
            || ($this->viewPermission == 'owned' && in_array($this->userId, $memberIds) && in_array('cleaner', user_roles()))
            || ($this->viewPermission == 'both' && ($this->userId == $this->site->client_id || $this->userId == $this->site->added_by))
            || ($this->viewPermission == 'both' && (in_array($this->userId, $memberIds) || $this->userId == $this->site->added_by) && in_array('cleaner', user_roles()))
           || (($this->viewPermission == 'none') && (!is_null(($this->site->mentionProject))) && in_array($this->userId, $mentionIds))
        ));

        $this->pageTitle = $this->site->project_name;

        $getCustomFieldGroupsWithFields = $this->site->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->messageSetting = MessageSetting::first();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        $tab = request('tab');

        switch ($tab) {
        case 'members':
            abort_403(!(
                $this->viewProjectMemberPermission == 'all'
            ));
            $this->view = 'sites.ajax.members';
            break;
        case 'milestones':
            $this->site = Site::with(['milestones' => function($query) {
                $query->withCount('service jobs');
            }])->findOrFail($id);
            $this->view = 'sites.ajax.milestones';
            break;
        case 'taskboard':
            session()->forget('pusher_settings');
            $this->view = 'sites.ajax.taskboard';
            break;
        case 'service jobs':
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->unAssignedTask = $this->site->service jobs()->whereDoesntHave('users')->count();
            return (!$this->site->trashed()) ? $this->service jobs($this->site->project_admin == $this->userId) : $this->archivedTasks($this->site->project_admin == $this->userId);
        case 'gantt':
            $this->hideCompleted = request('hide_completed') ?? 0;
            $this->ganttData = $this->ganttDataNew($this->site->id, $this->hideCompleted, $this->site->company);
            $this->taskBoardStatus = TaskboardColumn::all();

            $dateFormat = Company::DATE_FORMATS;
            $this->dateformat = (isset($dateFormat[$this->company->date_format])) ? $dateFormat[$this->company->date_format] : 'DD-MM-YYYY';

            $this->view = 'sites.ajax.gantt_dhtml';
            // $this->view = 'sites.ajax.gantt';
            break;
        case 'invoices':
            return $this->invoices();
        case 'quotes':
            return $this->quotes($this->site->id, $this->site->client_id);
        case 'files':
            $this->view = 'sites.ajax.files';
            break;
        case 'timelogs':
            return $this->timelogs($this->site->project_admin == $this->userId);
        case 'expenses':
            return $this->expenses();
        case 'miroboard';
            abort_403(!in_array($this->viewMiroboardPermission, ['all']) || !$this->site->enable_miroboard &&
                ((!in_array('customer', user_roles()) && !$this->site->client_access && $this->site->client_id != $this->userId)));
            $this->view = 'sites.ajax.miroboard';
            break;
        case 'payments':
            return $this->payments();
        case 'discussion':
            $this->discussionCategories = DiscussionCategory::orderBy('order', 'asc')->get();

            return $this->discussions($this->site->project_admin == $this->userId);
        case 'notes':
            return $this->notes($this->site->project_admin == $this->userId);
        case 'rating':
            return $this->rating($this->site->project_admin == $this->userId);
        case 'burndown-chart':
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);

            return $this->burndownChart($this->site);
        case 'activity':
            $this->activities = [];

            if(!in_array('customer', user_roles())){
                $this->activities = ProjectActivity::getProjectActivities($id, 10);
            }

            $this->view = 'sites.ajax.activity';
            break;
        case 'tickets':
            return $this->tickets($this->site->project_admin == $this->userId);
        case 'orders':
            return $this->orders();
        default:
            $this->taskChart = $this->taskChartData($id);
            // $hoursLogged = $this->site->times()->sum('total_minutes');
            $hoursLogged = $this->site->times()
            ->whereHas('service job', function ($query) {
                $query->whereNull('deleted_at'); // exclude soft-deleted service jobs
            })
            ->sum('total_minutes');


            $breakMinutes = ProjectTimeLogBreak::projectBreakMinutes($id);

            $this->hoursBudgetChart = $this->hoursBudgetChartData($this->site, $hoursLogged, $breakMinutes);

            $this->amountBudgetChart = $this->amountBudgetChartData($this->site);
            $this->taskBoardStatus = TaskboardColumn::all();
            
            // Calculate earnings with currency conversion (same logic as IncomeVsExpenseReportController)
            $payments = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
                ->where('payments.status', 'complete')
                ->where('payments.project_id', $id)
                ->get([
                    'payments.amount',
                    'currencies.id as currency_id',
                    'payments.exchange_rate',
                    'payments.default_currency_id'
                ]);

            $this->earnings = 0;
            $projectCurrencyId = $this->site->currency_id ?? company()->currency_id;

            foreach ($payments as $payment) {
                // Determine which exchange rate to use
                if ((is_null($payment->default_currency_id) && is_null($payment->exchange_rate)) ||
                    (!is_null($payment->default_currency_id) && company()->currency_id != $payment->default_currency_id)) {
                    $currency = Currency::findOrFail($payment->currency_id);
                    $exchangeRate = $currency->exchange_rate;
                } else {
                    $exchangeRate = $payment->exchange_rate;
                }

                // Convert to company currency first
                if ($payment->currency_id != company()->currency_id && $payment->amount > 0 && $exchangeRate > 0) {
                    $amountInCompanyCurrency = floatval($payment->amount) * floatval($exchangeRate);
                } else {
                    $amountInCompanyCurrency = floatval($payment->amount);
                }

                // Then convert to site currency if needed
                if ($projectCurrencyId != company()->currency_id) {
                    $projectCurrency = Currency::findOrFail($projectCurrencyId);
                    if ($projectCurrency->exchange_rate > 0) {
                        $this->earnings += $amountInCompanyCurrency / floatval($projectCurrency->exchange_rate);
                    } else {
                        $this->earnings += $amountInCompanyCurrency;
                    }
                } else {
                    $this->earnings += $amountInCompanyCurrency;
                }
            }

            // Initialize variables to store hours and minutes
            $this->hoursLogged = $this->convertMinutesToHoursAndMinutes($hoursLogged, $breakMinutes);

            // Calculate expenses with currency conversion (same logic as IncomeVsExpenseReportController)
            $expenseResults = Expense::join('currencies', 'currencies.id', '=', 'expenses.currency_id')
                ->where(['expenses.project_id' => $id, 'expenses.status' => 'approved'])
                ->get([
                    'expenses.price',
                    'currencies.id as currency_id',
                    'expenses.exchange_rate',
                    'expenses.default_currency_id'
                ]);

            $this->expenses = 0;

            foreach ($expenseResults as $expense) {
                // Determine which exchange rate to use
                if ((is_null($expense->default_currency_id) && is_null($expense->exchange_rate)) ||
                    (!is_null($expense->default_currency_id) && company()->currency_id != $expense->default_currency_id)) {
                    $currency = Currency::findOrFail($expense->currency_id);
                    $exchangeRate = $currency->exchange_rate;
                } else {
                    $exchangeRate = $expense->exchange_rate;
                }

                // Convert to company currency first
                if ($expense->currency_id != company()->currency_id && $expense->price > 0 && $exchangeRate > 0) {
                    $amountInCompanyCurrency = floatval($expense->price) * floatval($exchangeRate);
                } else {
                    $amountInCompanyCurrency = floatval($expense->price);
                }

                // Then convert to site currency if needed
                if ($projectCurrencyId != company()->currency_id) {
                    $projectCurrency = Currency::findOrFail($projectCurrencyId);
                    if ($projectCurrency->exchange_rate > 0) {
                        $this->expenses += $amountInCompanyCurrency / floatval($projectCurrency->exchange_rate);
                    } else {
                        $this->expenses += $amountInCompanyCurrency;
                    }
                } else {
                    $this->expenses += $amountInCompanyCurrency;
                }
            }

            $this->profit = $this->earnings - $this->expenses;

            $this->view = 'sites.ajax.overview';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('sites.show', $this->data);

    }

    // Convert minutes in hours and minutes
    public function convertMinutesToHoursAndMinutes($totalMinutes, $breakMinutes = 0)
    {
        $totalMinutes = ($totalMinutes - $breakMinutes);

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $hours.__('app.hour').' '. $minutes.__('app.minute');
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function taskChartData($id)
    {
        $taskStatus = TaskboardColumn::all();
        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        foreach ($taskStatus as $label) {
            $data['values'][] = Service Job::where('project_id', $id)->where('service jobs.board_column_id', $label->id)->count();
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function hoursBudgetChartData($site, $hoursLogged, $breakMinutes)
    {
        $hoursBudget = $site->hours_allocated ? $site->hours_allocated : 0;

        $hoursLogged = intdiv($hoursLogged - $breakMinutes, 60);
        $overRun = $hoursLogged - $hoursBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;
        $hoursLogged = ($hoursLogged > $hoursBudget) ? $hoursBudget : $hoursLogged;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $hoursBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$hoursBudget, $hoursLogged],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;
        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function amountBudgetChartData($site)
    {
        $amountBudget = $site->project_budget ?: 0;
        
        // Calculate earnings with currency conversion (same logic as IncomeVsExpenseReportController)
        $payments = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where('payments.status', 'complete')
            ->where('payments.project_id', $site->id)
            ->get([
                'payments.amount',
                'currencies.id as currency_id',
                'payments.exchange_rate',
                'payments.default_currency_id'
            ]);

        $earnings = 0;
        $projectCurrencyId = $site->currency_id ?? company()->currency_id;

        foreach ($payments as $payment) {
            // Determine which exchange rate to use
            if ((is_null($payment->default_currency_id) && is_null($payment->exchange_rate)) ||
                (!is_null($payment->default_currency_id) && company()->currency_id != $payment->default_currency_id)) {
                $currency = Currency::findOrFail($payment->currency_id);
                $exchangeRate = $currency->exchange_rate;
            } else {
                $exchangeRate = $payment->exchange_rate;
            }

            // Convert to company currency first
            if ($payment->currency_id != company()->currency_id && $payment->amount > 0 && $exchangeRate > 0) {
                $amountInCompanyCurrency = floatval($payment->amount) * floatval($exchangeRate);
            } else {
                $amountInCompanyCurrency = floatval($payment->amount);
            }

            // Then convert to site currency if needed
            if ($projectCurrencyId != company()->currency_id) {
                $projectCurrency = Currency::findOrFail($projectCurrencyId);
                if ($projectCurrency->exchange_rate > 0) {
                    $earnings += $amountInCompanyCurrency / floatval($projectCurrency->exchange_rate);
                } else {
                    $earnings += $amountInCompanyCurrency;
                }
            } else {
                $earnings += $amountInCompanyCurrency;
            }
        }
        
        $plannedOverun = $earnings < $amountBudget ? $earnings : $amountBudget;
        $overRun = $earnings - $amountBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $amountBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$amountBudget, $plannedOverun],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function storePin(Request $request)
    {
        $userId = UserService::getUserId();

        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->user_id = $userId;
        $pinned->added_by = user()->id;
        $pinned->save();

        return Reply::success(__('team chat.pinnedSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroyPin(Request $request, $id)
    {
        $userId = UserService::getUserId();
        Pinned::where('project_id', $id)->where('user_id', $userId)->delete();

        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function assignProjectAdmin(Request $request)
    {
        $userId = $request->userId;
        $projectId = $request->projectId;
        $site = Site::findOrFail($projectId);
        $site->project_admin = $userId;
        $site->save();

        return Reply::success(__('team chat.roleAssigned'));
    }

    public function service jobs($projectAdmin = false)
    {
        $dataTable = new TasksDataTable(true);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }
        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.service jobs';

        return $dataTable->render('sites.show', $this->data);

    }

    public function archivedTasks($projectAdmin = false)
    {
        $dataTable = new ArchiveTasksDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.service jobs';

        return $dataTable->render('sites.show', $this->data);

    }

    public function ganttData()
    {
        $id = request('projectID');
        $assignedTo = request('assignedTo');
        $projectTask = request('projectTask');
        $taskStatus = request('taskStatus');
        $milestones = request('milestones');
        $withoutDueDate = false;

        if ($assignedTo != 'all') {
            $service jobs = Service Job::projectTasks($id, $assignedTo, null, $withoutDueDate);
        }
        else {
            $service jobs = Service Job::projectTasks($id, null, null, $withoutDueDate);
        }

        if ($projectTask) {
            $service jobs = $service jobs->whereIn('id', explode(',', $projectTask));
        }

        if ($taskStatus) {
            $service jobs = $service jobs->whereIn('board_column_id', explode(',', $taskStatus));
        }

        if ($milestones != '') {
            $service jobs = $service jobs->whereIn('milestone_id', explode(',', $milestones));
        }

        $data = array();

        $count = 0;

        foreach ($service jobs as $service job) {
            $data[$count] = [
                'id' => 'service job-' . $service job->id,
                'name' => $service job->heading,
                'start' => ((!is_null($service job->start_date)) ? $service job->start_date->format('Y-m-d') : ((!is_null($service job->due_date)) ? $service job->due_date->format('Y-m-d') : null)),
                'end' => (!is_null($service job->due_date)) ? $service job->due_date->format('Y-m-d') : $service job->start_date->format('Y-m-d'),
                'progress' => 0,
                'bg_color' => $service job->boardColumn->label_color,
                'taskid' => $service job->id,
                'draggable' => true
            ];

            if (!is_null($service job->dependent_task_id)) {
                $data[$count]['dependencies'] = 'service job-' . $service job->dependent_task_id;
            }

            $count++;
        }

        return response()->json($data);
    }

    public function quotes($projectId, $clientId)
    {
        $dataTable = new EstimatesDataTable(
            $projectId,
            $clientId
        );

        $viewPermission = user()->permission('view_project_estimates');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';
        $this->view = 'sites.ajax.quotes';

        return $dataTable->render('sites.show', $this->data);
    }

    public function invoices()
    {
        $dataTable = new InvoicesDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.invoices';

        return $dataTable->render('sites.show', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceList(Request $request, $id)
    {
        $options = '<option value="">--</option>';

        $viewPermission = user()->permission('view_invoices');
        $userId = UserService::getUserId();

        if (($viewPermission == 'all' || $viewPermission == 'added')) {

            if ($id != 0) {
                $invoices = Invoice::with('payment', 'currency')->where('project_id', $id)->where('send_status', 1)->pending()->get();
            }
            else {
                $invoices = Invoice::with('payment')->where('send_status', 1)
                    ->where(function ($q) {
                        $q->where('status', 'unpaid')
                            ->orWhere('status', 'partial');
                    })->get();
            }

            foreach ($invoices as $item) {
                $paidAmount = $item->amountPaid();

                $options .= '<option data-currency-code="'.$item->currency->currency_code.'" data-currency-id="' . $item->currency_id . '" data-content="' . $item->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_format($item->total, $item->currency->id) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_format(max(($item->total - $paidAmount), 0), $item->currency->id) . '</span>" value="' . $item->id . '"> ' . $item->invoice_number . ' </option>';
            }

        }

        $bankData = '<option value="">--</option>';

        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankDetails = BankAccount::where('status', 1)->where('currency_id', $request->currencyId);

        if($this->viewBankAccountPermission == 'added'){
            $bankDetails = $bankDetails->where('added_by', $userId);
        }

        $bankDetails = $bankDetails->get();

        foreach ($bankDetails as $bankDetail) {

            $bankName = '';

            if($bankDetail->type == 'bank')
            {
                $bankName = $bankDetail->bank_name.' |';
            }

            $bankData .= '<option value="' . $bankDetail->id . '">'.$bankName .' '.$bankDetail->account_name. '</option>';
        }

        $exchangeRate = Currency::where('id', $request->currencyId)->pluck('exchange_rate')->toArray();

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'account' => $bankData, 'exchangeRate' => $exchangeRate]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function members($id)
    {
        $options = '';
        $userData = [];

        $site = Site::select('id', 'public')->find($id);
        $checkPublic = ($site) ? $site->public : 0;
        $userId = UserService::getUserId();

        if ($id == 0 || $checkPublic == 1) {
            $members = User::allEmployees(null, true);

            foreach ($members as $item) {
                $self_select = (user() && $userId == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option  data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div> ' . $item->name . '' . $self_select . '</span>" value="' . $item->id . '"> ' . $item->name . '</option>';
            }

            $projectShortCode = '--';
        }
        else {

            $members = ProjectMember::with('user')->where('project_id', $id)->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->get();


            foreach ($members as $item) {
                $content = ( $item->user->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                $self_select = (user() && $userId == $item->user->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option
                data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->user->image_url . ' ></div> ' . $item->user->name . '' . $self_select . '' . $content .'</span>"
                value="' . $item->user->id . '"> ' . $item->user->name . ' </option>';

                $url = route('cleaners.show', [$item->user->id]);

                $userData[] = ['id' => $item->user->id, 'value' => $item->user->name, 'image' => $item->user->image_url, 'link' => $url];
            }


            $site = Site::findOrFail($id);
            $projectShortCode = $site->project_short_code;

        }

        return Reply::dataOnly(['status' => 'success', 'unique_id' => $projectShortCode, 'data' => $options, 'userData' => $userData]);

    }

    public function timelogs($projectAdmin = false)
    {
        $dataTable = new TimeLogsDataTable($this->onlyTrashedRecords);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_timelogs');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.timelogs';

        return $dataTable->render('sites.show', $this->data);
    }

    public function expenses()
    {
        $dataTable = new ExpensesDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.expenses';

        return $dataTable->render('sites.show', $this->data);

    }

    public function payments()
    {
        $dataTable = new PaymentsDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_payments');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.payments';

        return $dataTable->render('sites.show', $this->data);

    }

    public function discussions($projectAdmin = false)
    {
        $dataTable = new DiscussionDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_discussions');
            abort_403(!in_array($viewPermission, ['all', 'added']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.discussion';

        return $dataTable->render('sites.show', $this->data);

    }

    public function burndown(Request $request, $id)
    {
        $this->site = Site::with(['service jobs' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $query->where(DB::raw('DATE(`start_date`)'), '>=', Carbon::createFromFormat($this->company->date_format, $request->startDate));
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $query->where(DB::raw('DATE(`due_date`)'), '<=', Carbon::createFromFormat($this->company->date_format, $request->endDate));
            }

            $query->whereNotNull('due_date');
        }])->withTrashed()->findOrFail($id);

        $this->totalTask = $this->site->service jobs->count();
        $datesArray = [];
        $startDate = $request->startDate ? Carbon::createFromFormat($this->company->date_format, $request->startDate) : Carbon::parse($this->site->start_date);

        if ($this->site->deadline) {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : Carbon::parse($this->site->deadline);
        }
        else {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : now();
        }

        for ($startDate; $startDate <= $endDate; $startDate->addDay()) {
            $datesArray[] = $startDate->format($this->company->date_format);
        }

        $uncompletedTasks = [];
        $createdTasks = [];
        $deadlineTasks = [];
        $deadlineTasksCount = [];
        $this->datesArray = json_encode($datesArray);

        foreach ($datesArray as $key => $value) {

            if (Carbon::createFromFormat($this->company->date_format, $value)->lessThanOrEqualTo(now())) {
                $uncompletedTasks[$key] = $this->site->service jobs->filter(function ($service job) use ($value) {

                    if (is_null($service job->completed_on)) {
                        return true;
                    }

                    return $service job->completed_on ? $service job->completed_on->greaterThanOrEqualTo(Carbon::createFromFormat($this->company->date_format, $value)) : false;
                })->count();

                $createdTasks[$key] = $this->site->service jobs->filter(function ($service job) use ($value) {
                    return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($service job->created_at->startOfDay());
                })->count();

                if ($key > 0) {
                    $uncompletedTasks[$key] += $createdTasks[$key];
                }

            }

            $deadlineTasksCount[] = $this->site->service jobs->filter(function ($service job) use ($value) {
                return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($service job->due_date->startOfDay());
            })->count();

            if ($key == 0) {
                $deadlineTasks[$key] = $this->totalTask - $deadlineTasksCount[$key];
            }
            else {
                $newKey = $key - 1;
                $deadlineTasks[$key] = $deadlineTasks[$newKey] - $deadlineTasksCount[$key];
            }
        }

        $this->uncompletedTasks = json_encode($uncompletedTasks);
        $this->deadlineTasks = json_encode($deadlineTasks);

        if ($request->ajax()) {
            return $this->data;
        }

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->format($this->company->date_format) : Carbon::parse($this->site->start_date)->format($this->company->date_format);
        $this->endDate = $endDate->format($this->company->date_format);

        return view('sites.ajax.burndown', $this->data);
    }

    public function notes($projectAdmin = false)
    {
        $dataTable = new ProjectNotesDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_note');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'sites.ajax.notes';

        return $dataTable->render('sites.show', $this->data);

    }

    public function tickets($projectAdmin = false)
    {
        $dataTable = new TicketDataTable($this->onlyTrashedRecords);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_tickets');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $this->activeTab = request()->tab ?: 'profile';
        $this->view = 'sites.ajax.tickets';

        return $dataTable->render('sites.show', $this->data);

    }

    public function burndownChart($site)
    {
        $viewPermission = user()->permission('view_project_burndown_chart');
        $userId = UserService::getUserId();
        abort_403(!(in_array($viewPermission, ['all']) || $site->project_admin == $userId));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'burndown-chart';
        $this->view = 'sites.ajax.burndown';

        return view('sites.show', $this->data);

    }

    public function rating($projectAdmin)
    {

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_rating');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $this->deleteRatingPermission = user()->permission('delete_project_rating');
        $this->editRatingPermission = user()->permission('edit_project_rating');
        $this->addRatingPermission = user()->permission('add_project_rating');


        $tab = request('tab');
        $this->activeTab = $tab ?: 'rating';


        $this->view = 'sites.ajax.rating';

        return view('sites.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchiveProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403($viewPermission == 'none');

        if (!request()->ajax()) {

            if (in_array('customer', user_roles())) {
                $this->customers = User::customer();
            }
            else {
                $this->customers = User::allClients();
                $this->allEmployees = User::allEmployees(null, true);
            }

            $this->categories = ProjectCategory::all();
            $this->zones = Team::all();
        }

        return $dataTable->render('sites.archive', $this->data);

    }

    public function archiveRestore($id)
    {
        $site = Site::withTrashed()->findOrFail($id);
        $site->restore();

        return Reply::success(__('team chat.projectRevertSuccessfully'));
    }

    public function importProject()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.sites');

        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'sites.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('sites.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ProjectImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('team chat.abortAction'));
        }

        $view = view('sites.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('team chat.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ProjectImport::class, ImportProjectJob::class);

        return Reply::successWithData(__('team chat.importProcessStart'), ['batch' => $batch]);
    }

    public function changeProjectStatus(Request $request)
    {
        $projectId = $request->projectId;
        $statusID = $request->statusId;
        $site = Site::with('members')->findOrFail($projectId);
        $projectUsers = $site->members->pluck('user_id')->toArray();

        $this->editProjectPermission = user()->permission('edit_projects');
        $userId = UserService::getUserId();

        abort_403(!(
            $this->editProjectPermission == 'all'
            || ($this->editProjectPermission == 'added' && $userId == $site->added_by)
            || ($this->editProjectPermission == 'owned' && $userId == $site->client_id && in_array('customer', user_roles()))
            || ($this->editProjectPermission == 'owned' && in_array($userId, $projectUsers) && in_array('cleaner', user_roles()))
            || ($this->editProjectPermission == 'both' && ($userId == $site->client_id || $userId == $site->added_by))
            || ($this->editProjectPermission == 'both' && in_array($userId, $projectUsers) && in_array('cleaner', user_roles())
            )));

        $projectStatus = ProjectStatusSetting::where('status_name', $statusID)->first();


        if ($projectStatus->status_name !== 'finished') {
            $this->handleNonFinishedStatus($site, $projectId, $projectStatus->status_name);
        } else {
            $response = $this->handleFinishedStatus($site, $projectId);

            if (!$response) {
                return Reply::error(__('team chat.projectTasksNotCompleted'));
            }
        }

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function pendingTasks($id)
    {
        $userId = UserService::getUserId();
        if ($id != 0) {
            $service jobs = Service Job::join('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->with('site')
                ->pending()
                ->where('task_users.user_id', '=', $userId)
                ->where('service jobs.project_id', '=', $id)
                ->select('service jobs.*')
                ->get();

        }
        else {
            $service jobs = Service Job::join('task_users', 'task_users.task_id', '=', 'service jobs.id')
                ->with('site')
                ->pending()
                ->where('task_users.user_id', '=', $userId)
                ->select('service jobs.*')
                ->get();
        }

        $options = '<option value="">--</option>';

        foreach ($service jobs as $item) {
            $name = '';

            if (!is_null($item->project_id)) {
                $name .= "<h5 class='f-12 text-darkest-grey'>" . $item->heading . "</h5><div class='text-muted f-11'>" . $item->site->project_name . '</div>';

            }
            else {
                $name .= "<span class='text-dark-grey f-11'>" . $item->heading . '</span>';
            }

            $options .= '<option data-content="' . $name . '" value="' . $item->id . '">' . $item->heading . '</option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);

    }

    public function ajaxLoadProject(Request $request)
    {
        $search = $request->search;

        $response = [];

        if ($search) {
            $lists = Site::allProjects($search);

            foreach ($lists as $list) {
                $response[] = [
                    'id' => $list->id,
                    'text' => $list->project_name,
                ];
            }
        }


        return response()->json($response);
    }

    public function duplicateProject($id)
    {
        $this->projectId = $id;

        $this->site = Site::findOrFail($id);
        $userId = UserService::getUserId();
        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();

        $addPermission = user()->permission('add_projects');
        $this->memberIds = $this->site->members->pluck('user_id')->toArray();
        $this->teams = Team::all();
        $this->departmentIds = $this->site->zones->pluck('team_id')->toArray();

        if ($this->site->zones->count() > 0) {
            $this->teamIds = $this->site->zones->pluck('team_id')->toArray();
            $this->cleaners = collect([]);

            foreach ($this->teamIds as $teamId) {
                $team = User::departmentUsers($teamId);
                $this->cleaners = $this->cleaners->merge($team);
            }
        }
        else {
            $this->cleaners = User::allEmployees(null, true, ($addPermission == 'all' ? 'all' : null));
        }

        $this->customers = User::allClients(null, false, ($addPermission == 'all' ? 'all' : null));

        if (in_array('customer', user_roles())) {
            $this->customer = User::withoutGlobalScope(ActiveScope::class)->findOrFail($userId);

        }
        else {
            $this->customer = isset(request()->default_client) ? User::withoutGlobalScope(ActiveScope::class)->findOrFail(request()->default_client) : null;
        }

        return view('sites.duplicate-site', $this->data);
    }

    public function storeDuplicateProject($request, $site)
    {
        $userId = UserService::getUserId();
        // For duplicate site
        if($request->has('file')){

            $projectExists = ProjectFile::where('project_id', $request->duplicateProjectID)->get();

            if ($projectExists) {
                foreach ($projectExists as $projectExist) {
                    $file = new ProjectFile();
                    $file->user_id = $projectExist->user_id;
                    $file->project_id = $site->id;

                    $fileName = Files::generateNewFileName($projectExist->filename);

                    Files::copy(ProjectFile::FILE_PATH . '/' . $projectExist->project_id . '/' . $projectExist->hashname, ProjectFile::FILE_PATH . '/' . $site->id . '/' . $fileName);

                    $file->filename = $projectExist->filename;
                    $file->hashname = $fileName;
                    $file->size = $projectExist->size;
                    $file->save();

                    $this->logProjectActivity($site->id, $userId, 'fileActivity', $site->board_column_id); /* @phpstan-ignore-line */
                }
            }

        }

        if($request->has('milestone')){

            $projectMilestoneExists = ProjectMilestone::where('project_id', $request->duplicateProjectID)->get();

            if ($projectMilestoneExists) {

                foreach ($projectMilestoneExists as $projectMilestoneExist) {
                    $milestone = new ProjectMilestone();
                    $milestone->project_id = $site->id;
                    $milestone->milestone_title = $projectMilestoneExist->milestone_title;
                    $milestone->summary = $projectMilestoneExist->summary;
                    $milestone->cost = $projectMilestoneExist->cost;
                    $milestone->currency_id = $projectMilestoneExist->currency_id;
                    $milestone->status = $projectMilestoneExist->status;
                    $milestone->start_date = $projectMilestoneExist->start_date;
                    $milestone->end_date = $projectMilestoneExist->end_date;
                    $milestone->save();

                    $this->logProjectActivity($milestone->project_id, 'team chat.milestoneUpdated');
                }
            }

        }

        if($request->has('time_sheet')){

            $projectTimeLogExists = ProjectTimeLog::where('project_id', $request->duplicateProjectID)->get();

            if ($projectTimeLogExists) {

                foreach ($projectTimeLogExists as $projectTimeLogExist) {
                    $projectTimeLog = new ProjectTimeLog();
                    $projectTimeLog->project_id = $site->id;
                    $projectTimeLog->task_id = $projectTimeLogExist->task_id;
                    $projectTimeLog->user_id = $projectTimeLogExist->user_id;
                    $projectTimeLog->start_time = $projectTimeLogExist->start_time;
                    $projectTimeLog->end_time = $projectTimeLogExist->end_time;
                    $projectTimeLog->total_hours = $projectTimeLogExist->total_hours;
                    $projectTimeLog->total_minutes = $projectTimeLogExist->total_minutes;
                    $projectTimeLog->hourly_rate = $projectTimeLogExist->hourly_rate;
                    $projectTimeLog->memo = $projectTimeLogExist->memo;
                    $projectTimeLog->edited_by_user = $userId;
                    $projectTimeLog->save();
                }
            }

        }

        if($request->has('note')){

            $projectNoteExists = ProjectNote::where('project_id', $request->duplicateProjectID)->get();

            if ($projectNoteExists) {

                foreach ($projectNoteExists as $projectNoteExist) {
                    $projectNote = new ProjectNote();
                    $projectNote->project_id = $site->id;
                    $projectNote->title = $projectNoteExist->title;
                    $projectNote->details = $projectNoteExist->details;
                    $projectNote->type = $projectNoteExist->type;
                    $projectNote->client_id = $projectNoteExist->client_id;
                    $projectNote->is_client_show = $projectNoteExist->is_client_show;
                    $projectNote->ask_password = $projectNoteExist->ask_password;
                    $projectNote->save();
                }
            }

        }

        if($request->has('service job')){

            $projectTasks = Service Job::with('labels', 'taskUsers');

            if($request->task_status){
                $projectTasks->whereIn('board_column_id', $request->task_status);
            }

            $projectTasks = $projectTasks->where('project_id', $request->duplicateProjectID)->get();
            $taskBoard = TaskboardColumn::where('slug', 'incomplete')->first();

            if ($projectTasks) {

                foreach ($projectTasks as $projectTask) {

                    $service job = new Service Job();
                    $service job->company_id = company()->id;
                    $service job->project_id = $site->id;
                    $service job->heading = $projectTask->heading;
                    $service job->description = trim_editor($projectTask->description);
                    $service job->start_date = $projectTask->start_date;
                    $service job->due_date = $projectTask->due_date;
                    $service job->task_category_id = $projectTask->category_id; /* @phpstan-ignore-line */
                    $service job->priority = $projectTask->priority;
                    $service job->board_column_id = $taskBoard->id;
                    $service job->dependent_task_id = $projectTask->dependent_task_id;
                    $service job->is_private = $projectTask->is_private;
                    $service job->billable = $projectTask->billable;
                    $service job->estimate_hours = $projectTask->estimate_hours;
                    $service job->estimate_minutes = $projectTask->estimate_minutes;
                    $service job->milestone_id = $projectTask->milestone_id;
                    $service job->repeat = $projectTask->repeat;
                    $service job->hash = md5(microtime());

                    if ($projectTask->repeat) {
                        $service job->repeat_count = $projectTask->repeat_count;
                        $service job->repeat_type = $projectTask->repeat_type;
                        $service job->repeat_cycles = $projectTask->repeat_cycles;
                    }

                    if ($site) {
                        $projectLastTaskCount = Service Job::projectTaskCount($site->id);
                        $service job->task_short_code = ($site) ? $site->project_short_code . '-' . ((int)$projectLastTaskCount + 1) : null;
                    }

                    $service job->saveQuietly();

                    $this->saveSubTask($projectTask, $service job, $request);
                }
            }
        }
    }

    public function saveSubTask($projectTask, $service job, $request)
    {
        if($request->has('same_assignee')){
            foreach($projectTask->taskUsers as $taskUsers){
                $taskUser = new TaskUser();
                $taskUser->task_id = $service job->id;
                $taskUser->user_id = $taskUsers->user_id;
                $taskUser->save();
            }
        }

        if (!is_null($projectTask->id) && $request->has('sub_task')) {

            $subTasks = SubTask::with(['files'])->where('task_id', $projectTask->id)->get();

            if ($subTasks) {
                foreach ($subTasks as $subTask) {
                    $subTaskData = new SubTask();
                    $subTaskData->title = $subTask->title;
                    $subTaskData->task_id = $service job->id;
                    $subTaskData->description = trim_editor($subTask->description);

                    if ($subTask->start_date != '' && $subTask->due_date != '') {
                        $subTaskData->start_date = $subTask->start_date;
                        $subTaskData->due_date = $subTask->due_date;
                    }

                    $subTaskData->assigned_to = $subTask->assigned_to;

                    $subTaskData->save();

                    if ($subTask->files) {
                        foreach ($subTask->files as $fileData) {
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
    }

    public function getProjects(Request $request)
    {
        $clientId = UserService::getUserId();

        $sites = Site::query()
            ->when(($request->requesterType == 'customer' && $request->clientId), function ($query) use ($request) {
                $query->where('client_id', $request->clientId);
            })
            ->when(($request->requesterType == 'cleaner' && $request->userId), function ($query) use ($request) {
                $query->whereHas('members', function ($q) use ($request) {
                    $q->where('user_id', $request->userId);
                })
                ->orWhere('public', 1);
            })
            ->get();

        return Reply::dataOnly(['sites' => $sites]);
    }

    public function orders()
    {
        $dataTable = new OrdersDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_orders');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'sites.ajax.orders';

        return $dataTable->render('sites.show', $this->data);
    }

    public function ganttDataNew($projectID, $hideCompleted, $company)
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        if ($hideCompleted == 0) {
            $milestones = ProjectMilestone::with(['service jobs' => function ($q) {
                return $q->whereNotNull('service jobs.start_date');
            }, 'service jobs.boardColumn'])->where('project_id', $projectID)->get();

        } else {
            $milestones = ProjectMilestone::with(['service jobs' => function ($q) use ($taskBoardColumn) {
                return $q->whereNotNull('service jobs.start_date')->where('service jobs.board_column_id', '<>', $taskBoardColumn->id);
            }, 'service jobs.boardColumn'])
            ->where('status', 'incomplete')
            ->where('project_id', $projectID)->get();
        }

        $nonMilestoneTasks = Service Job::whereNull('milestone_id')->whereNotNull('start_date')->with('boardColumn');

        if ($hideCompleted == 1) {
            $nonMilestoneTasks = $nonMilestoneTasks->where('service jobs.board_column_id', '<>', $taskBoardColumn->id);
        }

        $nonMilestoneTasks = $nonMilestoneTasks->where('project_id', $projectID)->get();

        $ganttData = [];
        $ganttData['data'] = [];
        $ganttData['links'] = [];

        foreach ($milestones as $key => $milestone) {
            $parentID = 'site-' . $milestone->id;

            $ganttData['data'][] = [
                'id' => $parentID,
                'text' => $milestone->milestone_title,
                'type' => 'site',
                'start_date' => $milestone->start_date->format('d-m-Y H:i'),
                'duration' => $milestone->start_date->diffInDays($milestone->end_date) + 1,
                'progress' => ($milestone->service jobs->count()) ? ($milestone->completionPercent() / 100) : 0,
                'parent' => 0,
                'milestone_status' => $milestone->status,
                'open' => ($milestone->status == 'incomplete'),
                'color' => '#cccccc',
                'textColor' => '#09203F',
                'linkable' => false,
                'priority' => ($key + 1)
            ];


            foreach ($milestone->service jobs as $key2 => $service job) {
                $taskUsers = '<div class="d-inline-flex align-items-center ml-1 text-dark w-180" data-service job-id="'.$service job->id.'">';

                foreach($service job->users as $item) {
                    $taskUsers .= '<img data-toggle="tooltip" class="taskEmployeeImg rounded-circle mr-1" data-original-title="'.$item->name.'"
                                                     src="'.$item->image_url.'">';
                }

                $taskUsers .= view('components.status', ['style' => 'color: ' . $service job->boardColumn->label_color, 'value' => $service job->boardColumn->column_name, 'color' => 'red'])->render() . '</div>';

                $ganttData['data'][] = [
                    'id' => $service job->id,
                    'text' => $service job->heading,
                    'text_user' => $taskUsers,
                    'type' => 'service job',
                    'start_date' => $service job->start_date->format('d-m-Y H:i'),
                    'duration' => (($service job->due_date) ? $service job->start_date->diffInDays($service job->due_date) + 1 : 1),
                    'parent' => $parentID,
                    // 'milestone_status' => $milestone->milestone_id,
                    'task_status' => $service job->board_column_id,
                    'priority' => ($key2 + 1),
                    'color' => $service job->boardColumn->label_color.'20',
                    'textColor' => '#09203F',
                    'view' => view('components.cards.service job-card', ['service job' => $service job, 'draggable' => false, 'company' => $company])->render()
                ];

                if (!is_null($service job->dependent_task_id)) {
                    $ganttData['links'][] = [
                        'id' => $service job->id,
                        'source' => $service job->dependent_task_id,
                        'target' => $service job->id,
                        'type' => 0
                    ];
                }
            }

            if ($milestone->service jobs->count()) {
                $ganttData['data'][] = [
                    'id' => 'milestone-' . $milestone->id,
                    'text' => $milestone->milestone_title,
                    'type' => 'milestone',
                    'start_date' => (($service job->due_date) ? $service job->due_date->format('d-m-Y H:i') : $service job->start_date->format('d-m-Y H:i')),
                    'milestone_status' => $milestone->status,
                    'task_status' => $service job->board_column_id,
                    'duration' => (($service job->due_date) ? $service job->start_date->diffInDays($service job->due_date) + 1 : 1),
                    'parent' => $parentID,
                ];

                $ganttData['links'][] = [
                    'id' => 'milestone-' . $milestone->id,
                    'source' => $service job->id,
                    'target' => 'milestone-' . $milestone->id,
                    'type' => 0
                ];
            }
        }

        foreach ($nonMilestoneTasks as $key2 => $service job) {
            $taskUsers = '<div class="d-inline-flex align-items-center ml-1 text-dark w-180" data-service job-id="'.$service job->id.'">';

            foreach($service job->users as $item) {
                $taskUsers .= '<img data-toggle="tooltip" class="taskEmployeeImg rounded-circle mr-1" data-original-title="'.$item->name.'"
                                                 src="'.$item->image_url.'">';
            }

            $taskUsers .= view('components.status', ['style' => 'color: ' . $service job->boardColumn->label_color, 'value' => $service job->boardColumn->column_name, 'color' => 'red'])->render() . '</div>';

            $ganttData['data'][] = [
                'id' => $service job->id,
                'text' => $service job->heading,
                'text_user' => $taskUsers,
                'type' => 'service job',
                'start_date' => $service job->start_date->format('d-m-Y H:i'),
                'duration' => (($service job->due_date) ? $service job->start_date->diffInDays($service job->due_date) : 1),
                'priority' => ($key2 + 1),
                'task_status' => $service job->board_column_id,
                'color' => $service job->boardColumn->label_color.'20',
                'textColor' => '#09203F',
                'view' => view('components.cards.service job-card', ['service job' => $service job, 'draggable' => false, 'company' => $company])->render()
            ];

            if (!is_null($service job->dependent_task_id)) {
                $ganttData['links'][] = [
                    'id' => $service job->id,
                    'source' => $service job->dependent_task_id,
                    'target' => $service job->id,
                    'type' => 0
                ];
            }

        }

        $ganttData['links'] = array_merge($ganttData['links'], GanttLink::where('project_id', $projectID)->select('id', 'type', 'source', 'target', 'type')->get()->toArray());

        return $ganttData;
    }

}
