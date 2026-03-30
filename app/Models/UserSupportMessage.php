<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSupportMessage extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(static function (UserSupportMessage $message) {
            if (! $message->company_id && $message->support) {
                $message->company_id = $message->support->company_id;
            }
        });
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(UserSupport::class, 'user_support_id');
    }
}
