<?php

namespace Modules\Timesheet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkOrderLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'project_id' => ['nullable', 'integer'],
        ];
    }
}
