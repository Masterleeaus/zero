<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectDepartment extends Pivot
{
    protected $table = 'project_departments';
    protected $hidden = ['project_id', 'team_id'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

}
