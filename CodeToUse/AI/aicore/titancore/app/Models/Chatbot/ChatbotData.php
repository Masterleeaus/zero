<?php

namespace App\Models\Chatbot;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotData extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'content',
        'type',
        'type_value',
        'path',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(static function (ChatbotData $data) {
            if (! $data->company_id && $data->chatbot) {
                $data->company_id = $data->chatbot->company_id;
            }
        });
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
