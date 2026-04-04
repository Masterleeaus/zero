<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests\Overlay;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'chatbot_id' => ['required', 'integer', 'exists:ext_chatbots,id'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
