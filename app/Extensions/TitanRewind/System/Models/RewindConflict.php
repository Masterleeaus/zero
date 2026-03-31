<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;

class RewindConflict extends Model
{
    protected $table = 'tz_rewind_conflicts';
    protected $fillable = [
        'company_id','team_id','user_id','case_id','process_id','entity_type','entity_id','conflict_type','severity',
        'status','message','details_json','resolution_hint','resolved_at','resolved_by_type','resolved_by_id',
    ];
    protected $casts = ['details_json' => 'array', 'resolved_at' => 'datetime'];
}
