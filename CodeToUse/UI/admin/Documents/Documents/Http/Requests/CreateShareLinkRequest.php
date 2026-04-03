<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShareLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('documents.share') || auth()->user()?->can('manage_documents');
    }

    public function rules(): array
    {
        return [
            'document_id' => 'required|integer',
            'expires_at' => 'nullable|date',
            'max_views' => 'nullable|integer|min:1|max:100000',
        ];
    }
}
