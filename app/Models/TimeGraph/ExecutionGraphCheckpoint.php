<?php

declare(strict_types=1);

namespace App\Models\TimeGraph;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionGraphCheckpoint extends Model
{
    public $timestamps = false;

    protected $table = 'execution_graph_checkpoints';

    protected $fillable = [
        'execution_graph_id',
        'event_id',
        'label',
        'state_snapshot',
        'created_at',
    ];

    protected $casts = [
        'state_snapshot' => 'array',
        'created_at'     => 'datetime',
    ];

    public function graph(): BelongsTo
    {
        return $this->belongsTo(ExecutionGraph::class, 'execution_graph_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ExecutionEvent::class, 'event_id');
    }
}
