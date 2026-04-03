<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'approver_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
