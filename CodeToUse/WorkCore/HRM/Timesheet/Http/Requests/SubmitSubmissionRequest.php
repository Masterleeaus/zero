<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'submitter_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
