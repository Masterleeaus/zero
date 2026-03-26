<?php

namespace App\Models\Team;

use App\Models\User;
use App\Traits\BelongsToCompany;
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
                $teamCompany = $member->team?->company_id;
                if ($teamCompany !== null) {
                    $member->company_id = $teamCompany;
                } elseif ($member->user) {
                    $member->company_id = $member->user->company_id ?? $member->user->team_id;
                } elseif (auth()->check()) {
                    $member->company_id = tenant();
                }
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
