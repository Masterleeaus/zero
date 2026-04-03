<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'nullable|in:general,swms',
            'category' => 'nullable|string|max:190',
            'subcategory' => 'nullable|string|max:190',
            'template_slug' => 'nullable|string|max:190',
            'status' => 'nullable|in:draft,review,approved,archived',
            'trade' => 'nullable|string|max:64',
            'role' => 'nullable|string|max:64',
            'effective_at' => 'nullable|date',
            'review_at' => 'nullable|date',
            'body_markdown' => 'nullable|string',
        ];
    }
}
