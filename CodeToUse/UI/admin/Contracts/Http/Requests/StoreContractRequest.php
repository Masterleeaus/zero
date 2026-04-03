<?php

namespace Modules\Contracts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'client_id' => ['nullable','integer'],
            'effective_date' => ['nullable','date'],
            'expiry_date' => ['nullable','date','after_or_equal:effective_date'],
            'notes' => ['nullable','string'],
            'body_html' => ['nullable','string'],
            'signers' => ['required','array','min:1'],
            'signers.*.name' => ['required','string','max:255'],
            'signers.*.email' => ['required','email'],
            'signers.*.role' => ['nullable','string'],
        ];
    }
}
