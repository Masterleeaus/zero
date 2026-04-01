<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobStage extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'sequence',
        'stage_type',
        'is_closed',
        'is_default',
        'is_invoiceable',
        'fold',
        'require_signature',
        'color',
        'description',
    ];

    protected $casts = [
        'is_closed'         => 'boolean',
        'is_default'        => 'boolean',
        'is_invoiceable'    => 'boolean',
        'fold'              => 'boolean',
        'require_signature' => 'boolean',
    ];

    protected $attributes = [
        'stage_type'        => 'order',
        'is_closed'         => false,
        'is_default'        => false,
        'is_invoiceable'    => false,
        'fold'              => false,
        'require_signature' => false,
        'color'             => '#FFFFFF',
        'sequence'          => 1,
    ];

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'stage_id');
    }

    public function scopeForOrders(Builder $query): Builder
    {
        return $query->where('stage_type', 'order')->orderBy('sequence');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeInvoiceable(Builder $query): Builder
    {
        return $query->where('is_invoiceable', true);
    }
}
