<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests\Overlay;

use Illuminate\Foundation\Http\FormRequest;

class ConversationFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
