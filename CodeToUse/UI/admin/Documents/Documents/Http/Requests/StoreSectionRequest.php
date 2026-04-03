<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:64',
            'label' => 'required|string|max:190',
            'content' => 'nullable|string',
            'order' => 'nullable|integer|min:0|max:1000',
        ];
    }
}
