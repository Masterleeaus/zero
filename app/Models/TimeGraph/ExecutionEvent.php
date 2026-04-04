<?php

declare(strict_types=1);

namespace App\Models\TimeGraph;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutionEvent extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $table = 'execution_events';

    protected $fillable = [
        'company_id',
        'graph_id',
        'parent_event_id',
        'subject_type',
        'subject_id',
        'event_class',
        'event_type',
        'actor_type',
        'actor_id',
        'payload',
        'occurred_at',
        'sequence',
        'created_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public function graph(): BelongsTo
    {
        return $this->belongsTo(ExecutionGraph::class, 'graph_id', 'graph_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_event_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_event_id');
    }
}
