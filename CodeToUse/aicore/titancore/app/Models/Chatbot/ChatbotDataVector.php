<?php

namespace App\Models\Chatbot;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotDataVector extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'chatbot_data_id',
        'content',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(static function (ChatbotDataVector $vector) {
            if (! $vector->company_id) {
                $vector->company_id = $vector->chatbot?->company_id
                    ?? $vector->data?->company_id;
            }
        });
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }

    public function data(): BelongsTo
    {
        return $this->belongsTo(ChatbotData::class, 'chatbot_data_id');
    }
}
