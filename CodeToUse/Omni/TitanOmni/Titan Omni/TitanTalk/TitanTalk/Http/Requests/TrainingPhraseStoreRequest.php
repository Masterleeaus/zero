<?php

namespace Modules\TitanTalk\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingPhraseStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'intent_id' => ['required'],
            'phrase' => ['required','string','max:500'],
        ];
    }
}
