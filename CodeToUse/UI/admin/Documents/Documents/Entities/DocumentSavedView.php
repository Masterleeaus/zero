<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentSavedView extends Model
{
    use BelongsToTenant;

    protected $table = 'document_saved_views';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'filters',
        'is_system',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_system' => 'boolean',
    ];
}
