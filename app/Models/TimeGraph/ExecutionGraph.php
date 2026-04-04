<?php

declare(strict_types=1);

namespace App\Models\TimeGraph;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutionGraph extends Model
{
    use BelongsToCompany;

    protected $table = 'execution_graphs';

    protected $fillable = [
        'company_id',
        'graph_id',
        'root_subject_type',
        'root_subject_id',
        'title',
        'status',
        'started_at',
        'completed_at',
        'event_count',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'event_count'  => 'integer',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(ExecutionEvent::class, 'graph_id', 'graph_id')->orderBy('sequence');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(ExecutionGraphCheckpoint::class, 'execution_graph_id');
    }
}
