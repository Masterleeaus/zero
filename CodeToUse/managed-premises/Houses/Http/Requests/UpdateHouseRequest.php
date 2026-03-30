<?php

namespace Modules\Houses\Http\Requests;

use App\Http\Requests\CoreRequest;

class UpdateHouseRequest extends CoreRequest
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
            'house_code' => 'required|unique:houses,house_code,null,null,company_id,' . company()->id,
            'house_name' => 'required',
        ];
    }

}
