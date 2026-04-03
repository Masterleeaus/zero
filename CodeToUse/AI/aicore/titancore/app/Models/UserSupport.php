<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSupport extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'user_support';

    protected $guarded = [];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (UserSupport $support) {
            if (! $support->company_id && $support->user) {
                $support->company_id = $support->user->company_id;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UserSupportMessage::class)->orderBy('created_at', 'asc');
    }

    public static function findByTicketId(string $ticketId): ?UserSupport
    {
        return static::query()
            ->where('ticket_id', $ticketId)
            ->first();
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_id';
    }
}
