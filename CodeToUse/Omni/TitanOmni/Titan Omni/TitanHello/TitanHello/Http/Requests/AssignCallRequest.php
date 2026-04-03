<?php

namespace Modules\TitanHello\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to_user_id' => ['nullable', 'integer'],
        ];
    }
}
