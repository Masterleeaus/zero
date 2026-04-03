<?php

namespace Modules\TitanHello\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutboundCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_number' => ['required', 'string', 'max:50'],
            'from_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
