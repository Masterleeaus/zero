<?php

namespace Modules\Engineerings\Http\Requests;
use App\Http\Requests\CoreRequest;

class WorkOrderRequest extends CoreRequest
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
            'nomor_wo'         => 'required|unique:workorders',
            'workrequest_id'   => 'required|unique:workorders',
            'complaint_id'     => 'required',
            'invoice_id'       => 'required',
            'priority'         => 'required',
            'category'         => 'required',
            'status'           => 'required',
            'work_description' => 'required',
            'schedule_start'   => 'required',
            'schedule_finish'  => 'required',
            'estimate_hours'   => 'required',
            'estimate_minutes' => 'required',
            'actual_start'     => 'required',
            'actual_finish'    => 'required',
            'actual_hours'     => 'required',
            'actual_minutes'   => 'required',
            'unit_id'          => 'required',
            'assets_id'        => 'required',
        ];

        return $rules;
    }

}
