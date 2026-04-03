<?php

namespace Modules\TitanHello\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'enabled' => ['nullable','boolean'],
            'default_language' => ['nullable','string','max:10'],
        ];
    }
}
