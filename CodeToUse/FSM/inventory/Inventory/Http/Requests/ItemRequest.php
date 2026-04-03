<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null && $this->user()->can('inventory.manage'); }
    public function rules(): array {
        return [
            'name'=>'required|string|max:255',
            'sku'=>'nullable|string|max:128',
            'qty'=>'required|integer|min:0',
            'category'=>'nullable|string|max:128',
            'unit_price'=>'nullable|numeric|min:0'
        ];
    }
}
