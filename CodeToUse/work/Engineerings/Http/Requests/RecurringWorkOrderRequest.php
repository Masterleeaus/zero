<?php

namespace Modules\Engineerings\Http\Requests;
use Carbon\Carbon;
use App\Http\Requests\CoreRequest;

class RecurringWorkOrderRequest extends CoreRequest
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
        $setting = company();

        $rules = [
            'nomor_wo'         => 'required|unique:workorders',
            'workrequest_id'   => 'required|unique:workorders',
            'priority'         => 'required',
            'category'         => 'required',
            'work_description' => 'required',
            'schedule_start'   => 'required',
            'schedule_finish'  => 'required',
            'estimate_hours'   => 'required',
            'estimate_minutes' => 'required',
            'unit_id'          => 'required',
            'assets_id'        => 'required',
        ];

        $rules = [
            'billing_cycle' => 'required'
        ];

        if (!$this->has('immediate_schedule')) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after:'.Carbon::now()->format($setting->date_format);
        }

        return $rules;
    }

}
