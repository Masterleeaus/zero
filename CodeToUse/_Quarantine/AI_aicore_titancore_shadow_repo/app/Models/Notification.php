<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'company_id',
    ];

    protected static function booted(): void
    {
        static::creating(static function (Notification $notification) {
            if (! $notification->company_id && $notification->notifiable_type === User::class) {
                $notification->company_id = User::query()
                    ->where('id', $notification->notifiable_id)
                    ->value('company_id');
            }
        });
    }
}
