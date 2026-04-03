<?php

namespace Modules\TrNotes\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreNotes extends CoreRequest
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
            'remark' => 'required',
            'module_name' => 'required',
            'table_name' => 'required',
        ];
    }

}
