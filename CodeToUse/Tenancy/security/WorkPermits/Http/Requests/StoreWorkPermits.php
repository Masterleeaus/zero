<?php

namespace Modules\TrWorkPermits\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreWorkPermits extends CoreRequest
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
            'date'              => 'required',
            'company_name'      => 'required',
            'company_address'   => 'required',
            'project_manj'      => 'required',
            'site_coor'         => 'required',
            'phone'             => 'required',
            'jenis_pekerjaan'   => 'required',
            'lingkup_pekerjaan' => 'required',
            'unit_id'           => 'required',
            'detail_pekerjaan'  => 'required',
            'date_start'        => 'required',
            'date_end'          => 'required',
        ];
    }
}
