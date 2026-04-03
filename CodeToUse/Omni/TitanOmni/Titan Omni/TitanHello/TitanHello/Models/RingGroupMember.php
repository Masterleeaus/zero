<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingGroupMember extends Model
{
    protected $table = 'titanhello_ring_group_members';

    protected $fillable = [
        'ring_group_id',
        'label',
        'phone_number',
        'priority',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'priority' => 'integer',
    ];

    public function ringGroup(): BelongsTo
    {
        return $this->belongsTo(RingGroup::class, 'ring_group_id');
    }
}
