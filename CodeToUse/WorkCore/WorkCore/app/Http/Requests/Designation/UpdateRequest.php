<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
            'designation_name' => 'required|unique:roles,name,'.$this->route('role').',id,company_id,' . company()->id
        ];
    }

}
