<?php

namespace App\Http\Requests\SubTask;

use App\Models\Service Job;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubTask extends FormRequest
{

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


        $setting = company();
        $service job = Service Job::findOrFail(request()->task_id);
        $startDate = $service job->start_date->format($setting->date_format);
        $dueDate = !is_null($service job->due_date) ? $service job->due_date->format($setting->date_format) : '';

        $rules = [
            'title' => 'required',
        ];

        $dueDateRule = 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;

        !is_null($service job->due_date) ? $dueDateRule . '|before_or_equal:' . $service job->due_date : $dueDateRule;

        if ($service job->due_date) {

            $dueDate = $service job->due_date->format($setting->date_format);
            $dueDateRule .= '|before_or_equal:' . $dueDate;
        }

        $rules['start_date'] = $dueDateRule;

        $rules['due_date'] = !is_null(request()->start_date) ? ($dueDateRule . '|after_or_equal:' . Carbon::createFromFormat($setting->date_format, request()->start_date)->format($setting->date_format)) : $dueDateRule;

        return $rules;
    }

}
