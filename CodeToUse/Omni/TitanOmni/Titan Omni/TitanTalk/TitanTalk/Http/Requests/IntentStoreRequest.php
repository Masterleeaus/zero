<?php

namespace Modules\TitanTalk\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntentStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:190'],
            'description' => ['nullable','string','max:2000'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
