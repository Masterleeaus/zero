<?php

namespace App\Models\Team;

use App\Models\User;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'team_id',
        'user_id',
        'role',
        'email',
        'status',
        'allow_unlimited_credits',
        'remaining_images',
        'remaining_words',
        'used_image_credit',
        'used_word_credit',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (TeamMember $member) {
            if ($member->company_id === null) {
                $teamCompany = $member->relationLoaded('team') ? $member->team?->company_id : null;
                $userCompany = $member->relationLoaded('user') ? $member->user?->company_id : null;

                $member->company_id = $teamCompany
                    ?? $userCompany
                    ?? (auth()->check() ? tenant() : null);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
