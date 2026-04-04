<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Requests\Overlay;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChannelCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::in(ChannelTypeEnum::values())],
            'credentials' => ['nullable', 'array'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
