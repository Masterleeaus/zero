<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;

class RewindFix extends Model
{
    protected $table = 'titan_rewind_fixes';

    protected $fillable = [
        'company_id','team_id','user_id','case_id','fix_type','proposed_by_type','proposed_by_id','requires_confirmation',
        'status','proposal_json','confirm_token','confirmed_at','confirmed_by_type','confirmed_by_id',
        'applied_at','applied_by_type','applied_by_id','result_json','error_text',
    ];

    protected $casts = [
        'requires_confirmation' => 'boolean','proposal_json' => 'array','result_json' => 'array',
        'confirmed_at' => 'datetime','applied_at' => 'datetime',
    ];
}
