<?php

namespace Modules\PropertyManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return function_exists('user') ? user()->permission('propertymanagement.update') !== 'none' : true;
    }

    public function rules(): array
    {
        return (new StorePropertyRequest())->rules();
    }
}
