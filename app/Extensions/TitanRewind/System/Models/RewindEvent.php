<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;

class RewindEvent extends Model
{
    protected $table = 'titan_rewind_events';
    public $timestamps = false;

    protected $fillable = [
        'company_id','team_id','user_id','case_id','event_type','entity_type','entity_id','actor_type','actor_id',
        'idempotency_key','payload_json','event_hash','prev_event_hash','created_at',
    ];

    protected $casts = ['payload_json' => 'array'];
}
