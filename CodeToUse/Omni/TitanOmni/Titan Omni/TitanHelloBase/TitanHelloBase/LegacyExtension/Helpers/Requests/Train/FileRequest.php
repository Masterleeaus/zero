<?php

namespace Extensions\TitanHello\Helpers\Requests;

use Extensions\TitanHello\Models\ExtVoiceChatbot;
use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'file'   => 'required|file',
        ];
    }
}
