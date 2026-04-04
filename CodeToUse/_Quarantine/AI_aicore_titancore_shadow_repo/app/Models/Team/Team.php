<?php

namespace App\Models\Team;

use App\Models\User;
use App\Models\Concerns\BelongsToCompany;
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
            if ($team->company_id === null) {
                $creator = $team->relationLoaded('user') ? $team->user : null;
                $team->company_id = $creator?->company_id ?? (auth()->check() ? tenant() : null);
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
