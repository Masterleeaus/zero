<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeBoardUser extends BaseModel
{

    use HasFactory;

    public function cleaners(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')->where('type', 'cleaner');
    }

    public function customers(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')->where('type', 'customer');
    }

}
