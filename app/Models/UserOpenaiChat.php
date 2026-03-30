<?php

namespace App\Models;

use App\Helpers\Classes\MarketplaceHelper;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserOpenaiChat extends Model
{
    use BelongsToCompany;

    protected $table = 'user_openai_chat';

    protected $guarded = [];

    protected $casts = [
        'company_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(static function (UserOpenaiChat $chat) {
            if ($chat->company_id === null && auth()->check()) {
                $chat->company_id = tenant();
            }

            if (MarketplaceHelper::isRegistered('ai-chat-pro') && ! auth()->check()) {
                $chat->is_guest = true;
            }
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UserOpenaiChatMessage::class);
    }

    public function messagesWithoutInitial(): HasMany
    {
        return $this->hasMany(UserOpenaiChatMessage::class)->where('response', '!==', 'First Initiation');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OpenaiGeneratorChatCategory::class, 'openai_chat_category_id', 'id');
    }
}
