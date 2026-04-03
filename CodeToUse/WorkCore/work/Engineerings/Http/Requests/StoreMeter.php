<?php

namespace Modules\Engineerings\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreMeter extends CoreRequest
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
            'type_bill' => 'required',
            'unit_id' => 'required',
            'end_meter' => 'required',
        ];
    }

}
