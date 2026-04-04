<?php

declare(strict_types=1);

namespace App\Models\Mesh;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeshCapabilityExport extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'capability_type',
        'capability_value',
        'available_count',
        'geographic_scope',
        'is_exported',
    ];

    protected $casts = [
        'geographic_scope' => 'array',
        'is_exported'      => 'boolean',
        'available_count'  => 'integer',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeExported(Builder $query): Builder
    {
        return $query->where('is_exported', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('capability_type', $type);
    }
}
