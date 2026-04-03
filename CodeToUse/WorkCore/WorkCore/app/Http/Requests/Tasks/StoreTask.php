<?php

namespace App\Http\Requests\Service Jobs;

use Carbon\Carbon;
use App\Models\Service Job;
use App\Models\Site;
use App\Models\TaskboardColumn;
use App\Models\ProjectMilestone;
use App\Http\Requests\CoreRequest;
use App\Models\TaskSetting;
use App\Traits\CustomFieldsRequestTrait;

class StoreTask extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $site = request('project_id') ? Site::findOrFail(request('project_id')) : null;

        if(!is_null($this->milestone_id))
        {
            $milestone = ProjectMilestone::findOrFail($this->milestone_id);
            $milestoneEndDate = $milestone->end_date ? Carbon::parse($milestone->end_date) : null;
        }
        else
        {
            $milestoneEndDate = null;
        }


        $setting = company();
        $taskSetting = TaskSetting::first();
        $unassignedPermission = user()->permission('create_unassigned_tasks');

        $user = user();

        $rules = [
            'heading' => 'required',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
            'priority' => 'required'
        ];

        $waitingApproval = TaskboardColumn::waitingForApprovalColumn();
        if($site == null){
            $rules['board_column_id'] = 'not_in:' . $waitingApproval->id;
        }else{
            if($site->need_approval_by_admin == 0) {
                $rules['board_column_id'] = 'not_in:' . $waitingApproval->id;
            }
        }


        if(in_array('customer', user_roles()) || $taskSetting->project_required == 'yes')
        {
            $rules['project_id'] = 'required';
        }

        if(!$this->has('without_duedate'))
        {
            if(is_null($milestoneEndDate))
            {
                $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
            }
            else
            {
                $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date|before_or_equal:'.$milestoneEndDate;
            }
        }


        if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
            $startDate = $site->start_date->format($setting->date_format);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;
        }
        else {
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format;
        }

        if ($this->has('dependent') && $this->dependent_task_id != '') {
            $dependentTask = Service Job::findOrFail($this->dependent_task_id);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:"' . $dependentTask->due_date->format($setting->date_format) . '"';
        }

        $rules['user_id.0'] = 'required_with:is_private';

        if ($unassignedPermission != 'all') {
            $rules['user_id.0'] = 'required';
        }

        $rules['dependent_task_id'] = 'required_with:dependent';

        if ($this->has('repeat')) {
            $rules['repeat_cycles'] = 'required|integer|min:1';
            $rules['repeat_count'] = 'required|numeric';
        }

        if ($this->has('set_time_estimate')) {
            $rules['estimate_hours'] = 'required|integer|min:0';
            $rules['estimate_minutes'] = 'required|integer|min:0';
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function team chat()
    {
        $site = request('project_id') ? Site::findOrFail(request('project_id')) : null;

        return [
            'project_id.required' => __('team chat.chooseProject'),
            'due_date.after_or_equal' => __('team chat.taskAfterDateValidation'),
            'due_date.before_or_equal' => __('team chat.taskBeforeDateValidation'),
            'board_column_id.not_in' => $site == null ? __('team chat.selectAnotherStatus') : __('team chat.selectStatus'),
        ];
    }

    public function attributes()
    {
        $attributes = [
            'user_id.0' => __('modules.service jobs.assignTo'),
            'dependent_task_id' => __('modules.service jobs.dependentTask'),
            'board_column_id' => __('modules.service jobs.status'),
        ];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
