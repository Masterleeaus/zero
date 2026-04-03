<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevokeShareLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('documents.share') || auth()->user()?->can('manage_documents');
    }

    public function rules(): array
    {
        return [
            'share_link_id' => 'required|integer',
        ];
    }
}
