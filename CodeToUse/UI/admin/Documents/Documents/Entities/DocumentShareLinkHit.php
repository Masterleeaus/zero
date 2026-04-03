<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentShareLinkHit extends Model
{
    use BelongsToTenant;

    protected $table = 'document_share_link_hits';

    protected $fillable = [
        'share_link_id',
        'document_id',
        'tenant_id',
        'user_id',
        'ip',
        'user_agent',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];
}
