<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimesheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'task_id' => ['nullable', 'integer'],
            'work_order_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'integer', 'min:0', 'max:24'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'type' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
