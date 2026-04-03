<?php

namespace Extensions\TitanHello\Helpers\Requests;

use Extensions\TitanHello\Helpers\Enums\TrainTypeEnum;
use Extensions\TitanHello\Models\ExtVoiceChatbot;
use Illuminate\Foundation\Http\FormRequest;

class DataRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'         => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'type'       => ['sometimes', 'nullable', 'in:' . implode(',', TrainTypeEnum::toArray())],
        ];
    }
}
