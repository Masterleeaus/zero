<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests\Overlay;

use Illuminate\Foundation\Http\FormRequest;

class CustomerMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:51200'],
        ];
    }
}
