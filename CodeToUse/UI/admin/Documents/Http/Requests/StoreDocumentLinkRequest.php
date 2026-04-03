<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'linked_type' => 'required|string|max:64',
            'linked_id' => 'required|integer|min:1',
            'label' => 'nullable|string|max:190',
        ];
    }
}
