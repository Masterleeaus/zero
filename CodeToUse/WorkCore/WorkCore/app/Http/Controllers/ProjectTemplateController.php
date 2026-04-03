<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectTemplateTasksDataTable;
use App\DataTables\ProjectTemplatesDataTable;
use App\Helper\Reply;
use App\Http\Requests\ProjectTemplate\StoreProject;
use App\Models\Site;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use App\Models\ProjectTemplate;
use App\Models\TaskboardColumn;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectTemplateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectTemplate';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('sites', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectTemplatesDataTable $dataTable)
    {
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        $this->viewProjectTemplatePermission = user()->permission('view_project_template');

        abort_403(!in_array($this->viewProjectTemplatePermission, ['all']) && !in_array($this->manageProjectTemplatePermission, ['all', 'added']));
        $this->categories = ProjectCategory::all();
        return $dataTable->render('site-templates.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.menu.addProjectTemplate');
        $this->categories = ProjectCategory::all();
        $this->cleaners = User::allEmployees();
        $this->view = 'site-templates.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('site-templates.create', $this->data);
    }

    /**
     * @param StoreProject $request
     * @return mixed|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProject $request)
    {

        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');

        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $site = new ProjectTemplate();
        $site->project_name = $request->project_name;

        if ($request->project_summary != '') {
            $site->project_summary = $request->project_summary;
        }

        if ($request->notes != '') {
            $site->notes = $request->notes;
        }

        if ($request->category_id != '') {
            $site->category_id = $request->category_id;
        }

        if ($request->sub_category_id != '') {
            $site->sub_category_id = $request->sub_category_id;
        }

        if ($request->client_view_task) {
            $site->client_view_task = 'enable';
        }
        else {
            $site->client_view_task = 'disable';
        }

        if (($request->client_view_task) && ($request->client_task_notification)) {
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

        $site->added_by = user()->id;

        $site->save();
        return Reply::dataOnly(['projectID' => $site->id]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->template = ProjectTemplate::with('milestones')->findOrFail($id);
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        $this->viewProjectTemplatePermission = user()->permission('view_project_template');

        abort_403(!in_array($this->viewProjectTemplatePermission, ['all']) && !in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $tab = request('tab');

        switch ($tab) {
        case 'members':
            $this->view = 'site-templates.ajax.members';
                break;
        case 'milestones':

            $this->site =  $this->template;
            $this->view = 'site-templates.ajax.milestones';
            break;
        case 'service jobs':
            $this->taskBoardStatus = TaskboardColumn::all();
                return $this->service jobs();
        default:
            $this->view = 'site-templates.ajax.overview';
                break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('site-templates.show', $this->data);


    }

    public function service jobs()
    {

        $manageProjectTemplatePermission = user()->permission('manage_project_template');

        abort_403(in_array($this->viewProjectTemplatePermission, ['none']) && in_array($this->manageProjectTemplatePermission, ['none', 'both']));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'site-templates.ajax.service jobs';

        $dataTable = new ProjectTemplateTasksDataTable();
        return $dataTable->render('site-templates.show', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->template = ProjectTemplate::findOrFail($id);
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.update') . ' ' . __('app.site');

        $this->categoryData = collect();

        if (!is_null($this->template->category_id)) {
            $this->categoryData = ProjectSubCategory::where('category_id', $this->template->category_id)->get();
        }
        
        $this->categories = ProjectCategory::all();
        $this->view = 'site-templates.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('site-templates.create', $this->data);

    }

    /**
     * @param StoreProject $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreProject $request, $id)
    {
        $site = ProjectTemplate::findOrFail($id);
        $site->project_name = $request->project_name;

        if ($request->project_summary != '') {
            $site->project_summary = $request->project_summary;
        }

        if ($request->notes != '') {
            $site->notes = trim_editor($request->notes);
        }

        if ($request->category_id != '') {
            $site->category_id = $request->category_id;
        }

        if ($request->sub_category_id != '') {
            $site->sub_category_id = $request->sub_category_id;
        }else{
            $site->sub_category_id = null;
        }


        if ($request->client_view_task) {
            $site->client_view_task = 'enable';
        }
        else {
            $site->client_view_task = 'disable';
        }

        if (($request->client_view_task) && ($request->client_task_notification)) {
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

        $site->client_id = $request->client_id;
        $site->feedback = $request->feedback;

        $site->save();
        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => route('site-template.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');

        if(!in_array($this->manageProjectTemplatePermission, ['all', 'added'])) {

            return Reply::error(__('team chat.permissionDenied'));
        }

        ProjectTemplate::destroy($id);
        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->manageProjectTemplatePermission = user()->permission('manage_project_template');

            if(!in_array($this->manageProjectTemplatePermission, ['all', 'added'])) {

                return Reply::error(__('team chat.permissionDenied'));
            }

            $this->deleteRecords($request);
            return Reply::success(__('team chat.deleteSuccess'));

        default:
                return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        ProjectTemplate::whereIn('id', explode(',', $request->row_ids))->delete();
    }

}
