<?php

namespace Extensions\TitanHello\Helpers\Requests;

use Extensions\TitanHello\Models\ExtVoiceChatbot;
use Extensions\TitanHello\Models\ExtVoicechatbotTrain;
use Illuminate\Foundation\Http\FormRequest;

class TrainRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'data'   => 'required|array',
            'data.*' => 'required|exists:' . (new ExtVoicechatbotTrain)->getTable() . ',id',
        ];
    }
}
