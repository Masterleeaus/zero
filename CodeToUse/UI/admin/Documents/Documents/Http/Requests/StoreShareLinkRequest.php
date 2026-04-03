<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'expires_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'max_views' => 'nullable|integer|min:1|max:100000',
        ];
    }
}
