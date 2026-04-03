<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:190',
            'category' => 'nullable|string|max:64',
            'body_markdown' => 'nullable|string',
            'trade' => 'nullable|string|max:64',
            'role' => 'nullable|string|max:64',
        ];
    }
}
