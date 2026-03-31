<?php

namespace App\Extensions\TitanRewind\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewindCase extends Model
{
    protected $table = 'titan_rewind_cases';

    protected $fillable = [
        'company_id','team_id','user_id',
        'title','status','severity',
        'source_type','source_id',
        'process_id','correction_process_id','replacement_process_id','entity_type','entity_id',
        'detected_at','meta_json',
        'resolved_at','resolved_by_type','resolved_by_id','rollback_completed_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'rollback_completed_at' => 'datetime',
        'meta_json' => 'array',
    ];

    public function events(): HasMany { return $this->hasMany(RewindEvent::class, 'case_id'); }
    public function fixes(): HasMany { return $this->hasMany(RewindFix::class, 'case_id'); }
    public function links(): HasMany { return $this->hasMany(RewindLink::class, 'case_id'); }
    public function conflicts(): HasMany { return $this->hasMany(RewindConflict::class, 'case_id'); }
    public function snapshots(): HasMany { return $this->hasMany(RewindSnapshot::class, 'case_id'); }
}

