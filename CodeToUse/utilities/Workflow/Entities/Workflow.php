<?php

namespace Modules\Workflow\Entities;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $table = 'workflows';
    protected $guarded = [];
    protected $casts = [
        'workflow_data' => 'array',
    ];
}
