<?php

namespace Modules\TrInOutPermit\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreTrInOutPermit extends CoreRequest
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
            'pembawa_brg' => 'required',
            'date' => 'required',
            'pj' => 'required',
            'no_hp' => 'required',
            'name' => 'required',
            'keterangan' => 'required',
            'jenis_barang' => 'required',
        ];
    }

}
