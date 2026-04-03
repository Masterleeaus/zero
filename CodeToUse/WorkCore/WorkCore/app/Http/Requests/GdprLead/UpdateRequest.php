<?php

namespace App\Http\Requests\GdprLead;

use App\Models\Deal;
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
        $enquiry = Deal::whereRaw('md5(id) = ?', $this->route('enquiry'))->firstOrFail();

        $rules = [
            'company_name' => 'required',
            'client_name' => 'required',
            'client_email' => 'required|email:rfc,strict|unique:enquiries,client_email,'.$enquiry->id.',id,company_id,' . company()->id,
        ];

        return $rules;
    }

}
