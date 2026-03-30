<?php

namespace Modules\Engineerings\Http\Requests;
use App\Http\Requests\CoreRequest;

class WrRequest extends CoreRequest
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
        $rules = [
            'wr_no' => 'required|unique:workrequests',
            'complaint_id' => 'required',
            'check_time' => 'required',
            'remark' => 'required',
        ];
        $rules['user_id'] = 'required';

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.required' => __('modules.projects.selectClient')
        ];
    }

}
