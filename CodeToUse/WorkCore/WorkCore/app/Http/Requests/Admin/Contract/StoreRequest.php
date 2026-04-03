<?php

namespace App\Http\Requests\Admin\Service Agreement;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StoreRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->contract_number) {
            $this->merge([
                'contract_number' => \App\Helper\NumberFormat::service agreement($this->contract_number),
            ]);
        }
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
            'contract_number' => [
                'required',
                Rule::unique('service agreements')->where('company_id', company()->id)
            ],
            'client_id' => 'required',
            'subject' => 'required',
            'amount' => 'required',
            'contract_type' => 'required|exists:contract_types,id',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
