<?php

namespace Modules\Workflow\Entities;

use Illuminate\Database\Eloquent\Model;

class WorkflowSetting extends Model
{
    protected $table = 'workflow_settings';

    protected $fillable = [
        'company_id', 'key', 'value', 'updated_by'
    ];

    protected $casts = [
        'value' => 'array',
    ];
}
