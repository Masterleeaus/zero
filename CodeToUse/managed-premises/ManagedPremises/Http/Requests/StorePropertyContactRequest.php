<?php

namespace Modules\ManagedPremises\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('managedpremises.contacts.create') ?? false;
    }

    public function rules(): array
    {
        return ['name'=>['required','string','max:150'],'role'=>['required','string','max:60'],'phone'=>['nullable','string','max:60'],'email'=>['nullable','email','max:190'],'notes'=>['nullable','string','max:2000']];
    }
}
