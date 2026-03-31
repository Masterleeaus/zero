<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;

class RewindLink extends Model
{
    protected $table = 'tz_rewind_links';
    protected $fillable = [
        'company_id','team_id','user_id','case_id','parent_process_id','child_process_id','parent_entity_type','parent_entity_id',
        'child_entity_type','child_entity_id','relationship_type','depth','can_reuse','must_reissue','status','action_required','held_reason','meta_json',
    ];
    protected $casts = [
        'depth' => 'integer',
        'can_reuse' => 'boolean',
        'must_reissue' => 'boolean',
        'meta_json' => 'array',
    ];
}
