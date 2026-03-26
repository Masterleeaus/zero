<?php

namespace App\Models\Team;

use App\Models\User;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'is_shared',
        'name',
        'allow_seats',
        'used_image_credit',
        'word_credit',
        'entity_credits',
    ];

    protected $casts = [
        'entity_credits'    => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(static function (Team $team) {
            $creator = $team->user ?? auth()->user();
            if ($team->company_id === null && $creator) {
                $team->company_id = $creator->company_id ?? $creator->team_id;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'team_id');
    }

    public function getCredit(string $engineKey, string $entityKey): array
    {
        return $this->entity_credits[$engineKey][$entityKey] ?? [
            'credit'      => 0,
            'isUnlimited' => false,
        ];
    }
}
