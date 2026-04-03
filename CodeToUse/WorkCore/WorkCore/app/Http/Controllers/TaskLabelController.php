<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\TaskLabel\StoreRequest;
use App\Models\Site;
use App\Models\Service Job;
use App\Models\TaskLabel;
use App\Models\TaskLabelList;
use App\Models\ProjectTemplateTask;
use Illuminate\Http\Request;

class TaskLabelController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskLabel';
    }

    public function create()
    {
        $this->taskLabels = TaskLabelList::all();
        $this->sites = Site::all();
        $this->taskId = request()->task_id;
        $this->projectTemplateTaskId = request()->project_template_task_id;
        $this->projectId = request()->project_id;
        return view('service jobs.create_label', $this->data);
    }

    public function store(StoreRequest $request)
    {
        abort_403(user()->permission('task_labels') !== 'all');
        $taskLabel = new TaskLabelList();
        $this->storeLabel($request, $taskLabel);

        if ($request->parent_project_id != '') {
            $allTaskLabels = TaskLabelList::whereNull('project_id')->orWhere('project_id', $request->parent_project_id)->get();

        } else  {
            $allTaskLabels = TaskLabelList::whereNull('project_id')->get();
        }

        if($request->task_id){
            $service job = Service Job::with('label')->findOrFail($request->task_id);
            $currentTaskLable = $service job->label;
        }elseif($request->project_template_task_id){
            $service job = ProjectTemplateTask::findOrFail($request->project_template_task_id);
            $currentTaskLable = explode(',', $service job->task_labels);
        }
        else {
            $currentTaskLable = collect([]);
        }

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {

            $selected = '';

            foreach ($currentTaskLable as $item){
                if (is_object($item) && $item->label_id == $value->id) {
                    $selected = 'selected';
                } elseif (is_string($item) && $item == $value->id) {
                    $selected = 'selected';
                }
            }

            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>" '.$selected.'>' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['data' => $labels]);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('task_labels') !== 'all');

        $taskLabel = TaskLabelList::findOrFail($id);

        $this->storeUpdate($request, $taskLabel);

        if ($request->parent_project_id != '') {
            $allTaskLabels = TaskLabelList::whereNull('project_id')->orWhere('project_id', $request->parent_project_id)->get();

        } else  {
            $allTaskLabels = TaskLabelList::whereNull('project_id')->get();
        }

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {
            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>">' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['data' => $labels]);
    }

    private function storeLabel($request, $taskLabel)
    {
        $taskLabel->label_name = trim($request->label_name);
        $taskLabel->description = trim_editor($request->description);

        $taskLabel->project_id = $request->project_id;

        if ($request->has('color')) {
            $taskLabel->color = $request->color;
        }

        $taskLabel->save();

        return $taskLabel;
    }

    private function storeUpdate($request, $taskLabel)
    {

        if($request->label_name != null){
            $taskLabel->label_name = trim($request->label_name);
        }

        if($request->description != null){
            $taskLabel->description = trim_editor($request->description);
        }

        $oldProjectId = $taskLabel->project_id;
        $newProjectId = $request->project_id;

        if ($request->has('project_id')) {
            $taskLabel->project_id = $newProjectId;
        }

        if ($request->has('color')) {
            $taskLabel->color = $request->color;
        }

        if ($oldProjectId != $newProjectId) {

            $tasksWithOldProject = TaskLabel::where('label_id', $taskLabel->id)
                ->get();

            foreach ($tasksWithOldProject as $service job) {
                $service job->delete();
            }
        }

        $taskLabel->save();

        return $taskLabel;
    }

    public function destroy($id)
    {
        abort_403(user()->permission('task_labels') !== 'all');

        TaskLabelList::destroy($id);

        $allTaskLabels = TaskLabelList::all();

        if(request()->taskId){
            $service job = Service Job::with('label')->findOrFail(request()->taskId);
            $currentTaskLable = $service job->label;

        } elseif(request()->projectTemplateTaskId){
            $service job = ProjectTemplateTask::findOrFail(request()->projectTemplateTaskId);
            $currentTaskLable = explode(',', $service job->task_labels);
        } else {

            $currentTaskLable = collect([]);
        }

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {

            $selected = '';

            foreach ($currentTaskLable as $item){
                if (is_object($item) && $item->label_id == $value->id) {
                    $selected = 'selected';
                } elseif (is_string($item) && $item == $value->id) {
                    $selected = 'selected';
                }
            }

            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>" '.$selected.'>' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['data' => $labels]);
    }

    public function labels($id)
    {
        $options = '';

        if ($id == 0) {
            $labels = TaskLabelList::whereNull('project_id')->get();
        }
        else{
            $labels = TaskLabelList::where('project_id', $id)->orWhereNull('project_id')->get();
        }

        foreach ($labels as $item) {
            $options .= '<option value="' . $item->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $item->label_color . '\'>' . $item->label_name . '</span>" >' . $item->label_name . '</option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

}

