<?php

namespace Modules\Kontrak\Http\Requests;
use App\Http\Requests\CoreRequest;

class StoreKontrak extends CoreRequest
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
            'contract_number' => 'required|unique:contracts,contract_number,null,id,company_id,' . company()->id,
            'client_id' => 'required',
            'subject' => 'required',
            'amount' => 'required',
            'contract_type' => 'required|exists:contract_types,id',
        ];

        return $rules;
    }

}
