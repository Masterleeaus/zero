<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests\Overlay;

use Illuminate\Foundation\Http\FormRequest;

class ClaimConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }
}
