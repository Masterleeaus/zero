<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimesheetSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'costing_enabled' => ['required', 'boolean'],
            'timer_enabled' => ['required', 'boolean'],
            'approvals_enabled' => ['required', 'boolean'],
        ];
    }
}
