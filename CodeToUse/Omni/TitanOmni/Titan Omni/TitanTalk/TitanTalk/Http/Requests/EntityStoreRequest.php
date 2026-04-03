<?php

namespace Modules\TitanTalk\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntityStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:190'],
            'type' => ['nullable','string','max:190'],
            'synonyms' => ['nullable','array'],
        ];
    }
}
