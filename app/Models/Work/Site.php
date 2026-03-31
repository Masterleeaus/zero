<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'territory_id',
        'parent_id',
        'name',
        'reference',
        'address',
        'direction',
        'status',
        'start_date',
        'deadline',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline'   => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Site::class, 'parent_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'site_workers', 'site_id', 'user_id')
            ->withPivot('sequence')
            ->orderByPivot('sequence');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getCompleteNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->complete_name . ' / ' . $this->name;
        }

        return $this->name;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeRootSites(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForTerritory(Builder $query, int $territoryId): Builder
    {
        return $query->where('territory_id', $territoryId);
    }
}
