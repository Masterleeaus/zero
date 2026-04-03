<?php

namespace App\Observers;

use App\Models\ProjectTemplateTask;
use App\Models\Service Job;
use App\Models\TaskLabelList;

class TaskLabelListObserver
{

    public function creating(TaskLabelList $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function updated($taskLabel)
    {
        if ($taskLabel->isDirty('project_id') && request()->task_id != null) {

            $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();

            $projectTemplateTasks = ProjectTemplateTask::all();

            foreach ($projectTemplateTasks as $service job) {

                $taskLabelsArray = explode(',', $service job->task_labels);

                $updatedTaskLabels = array_filter($taskLabelsArray, function($labelId) use ($validLabelIds) {
                    return in_array($labelId, $validLabelIds);
                });

                if (implode(',', $updatedTaskLabels) !== $service job->task_labels) {
                    $service job->task_labels = implode(',', $updatedTaskLabels);
                    $service job->save();
                }
            }

            $service job = Service Job::with('labels')->findOrFail(request()->task_id);

            if ($service job->project_id != $taskLabel->project_id) {
                $service job->labels()->detach(request()->label_id);
            }

        }
    }

    public function deleted(){

        $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();

        $projectTemplateTasks = ProjectTemplateTask::all();

        foreach ($projectTemplateTasks as $service job) {

            $taskLabelsArray = explode(',', $service job->task_labels);

            $updatedTaskLabels = array_filter($taskLabelsArray, function($labelId) use ($validLabelIds) {
                return in_array($labelId, $validLabelIds);
            });

            if (implode(',', $updatedTaskLabels) !== $service job->task_labels) {
                $service job->task_labels = implode(',', $updatedTaskLabels);
                $service job->save();
            }
        }
    }

}


