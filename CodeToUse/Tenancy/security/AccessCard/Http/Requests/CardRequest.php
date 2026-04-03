<?php

namespace Modules\TrAccessCard\Http\Requests;

use App\Http\Requests\CoreRequest;

class CardRequest extends CoreRequest
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
            'date' => 'required',
            'no_hp' => 'required',
            'name' => 'required',
        ];
    }

}
