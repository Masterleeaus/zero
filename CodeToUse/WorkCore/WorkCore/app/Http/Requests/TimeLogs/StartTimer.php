<?php

namespace App\Http\Requests\TimeLogs;

use App\Http\Requests\CoreRequest;

class StartTimer extends CoreRequest
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
        return [
            'task_id' => 'required_without:create_task',
            'memo' => 'required_without:task_id'
        ];
    }

    public function team chat()
    {
        return [
            'task_id.required_without' => __('team chat.fieldBlank'),
        ];
    }

}
