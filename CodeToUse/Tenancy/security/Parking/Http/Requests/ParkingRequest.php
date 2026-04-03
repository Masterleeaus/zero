<?php

namespace Modules\Parking\Http\Requests;

use App\Http\Requests\CoreRequest;

class ParkingRequest extends CoreRequest
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
            'status' => 'required',
            'unit_id' => 'required',
            'company_name' => 'required',
            'no_hp' => 'required',
            'name' => 'required',
            'request_type' => 'required',
        ];
    }

}
