<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StopTimerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'convert' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
