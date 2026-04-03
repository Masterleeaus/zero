<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null && $this->user()->can('inventory.manage'); }
    public function rules(): array {
        return [
            'name'=>'required|string|max:255',
            'code'=>'nullable|string|max:64',
            'location'=>'nullable|string|max:255'
        ];
    }
}
