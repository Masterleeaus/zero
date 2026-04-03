<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RingGroup extends Model
{
    protected $table = 'titanhello_ring_groups';

    protected $fillable = [
        'company_id',
        'name',
        'strategy', // simultaneous | round_robin | sequential
        'timeout_seconds',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'timeout_seconds' => 'integer',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(RingGroupMember::class, 'ring_group_id');
    }
}
