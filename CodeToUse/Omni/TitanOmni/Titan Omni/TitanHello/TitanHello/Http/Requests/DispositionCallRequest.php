<?php

namespace Modules\TitanHello\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispositionCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disposition' => ['nullable', 'string', 'max:100'],
            'disposition_notes' => ['nullable', 'string', 'max:2000'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }
}
