<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreTicket extends CoreRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['subject'] = 'required';
        $rules['description'] = [
            'required',
            function ($attribute, $value, $fail) {
                $comment = trim_editor($value);;

                if ($comment == '') {
                    $fail(__('validation.required'));
                }
            }
        ];
        $rules['priority'] = 'required';
        $rules['user_id'] = 'required_if:requester_type,cleaner';
        $rules['client_id'] = 'required_if:requester_type,customer';
        $rules['group_id'] = 'required';
        $rules['project_id'] = 'nullable|exists:sites,id';

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    public function team chat()
    {
        return [
            'user_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
            'client_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
        ];
    }

}
