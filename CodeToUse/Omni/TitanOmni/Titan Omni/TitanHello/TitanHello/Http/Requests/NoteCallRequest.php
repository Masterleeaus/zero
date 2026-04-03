<?php

namespace Modules\TitanHello\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'max:5000'],
        ];
    }
}
