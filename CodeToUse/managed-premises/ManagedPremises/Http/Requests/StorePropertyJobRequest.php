<?php

namespace Modules\ManagedPremises\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('managedpremises.jobs.create') ?? false;
    }

    public function rules(): array
    {
        return ['title'=>['required','string','max:190'],'description'=>['nullable','string','max:5000'],'status'=>['nullable','string','max:40'],'scheduled_for'=>['nullable','date'],'linked_module'=>['nullable','string','max:80'],'linked_id'=>['nullable','integer']];
    }
}
