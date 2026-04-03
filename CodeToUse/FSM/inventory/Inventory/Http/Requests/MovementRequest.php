<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovementRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null && $this->user()->can('inventory.manage'); }
    public function rules(): array {
        return [
            'item_id'=>'required|integer',
            'warehouse_id'=>'nullable|integer',
            'type'=>'required|in:in,out,adjust',
            'qty_change'=>'required|integer|not_in:0',
            'note'=>'nullable|string'
        ];
    }
}
