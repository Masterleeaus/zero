<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;

class RewindSnapshot extends Model
{
    protected $table = 'tz_rewind_snapshots';

    protected $fillable = [
        'company_id','team_id','user_id','case_id','snapshot_key','snapshot_stage','snapshot_scope',
        'process_id','entity_type','entity_id','link_id','source_table','source_pk','captured_at',
        'before_json','after_json','meta_json',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'before_json' => 'array',
        'after_json' => 'array',
        'meta_json' => 'array',
    ];
}
