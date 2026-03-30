<?php

namespace Modules\Workflow\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkflowLog extends Model
{
    protected $table = 'workflow_logs';

    protected $fillable = [
        'company_id',
        'workflow_id','step_id','level','message','context','actor_id'
    ];

    protected $casts = [
        'context' => 'array',
    ];


    protected static function booted(): void
    {
        // Tenant safety: default company scope when available.
        if (Auth::check() && isset(Auth::user()->company_id)) {
            static::addGlobalScope('company', function ($query) {
                $query->where($query->getModel()->getTable().'.company_id', Auth::user()->company_id);
            });
        }

        static::creating(function ($model) {
            if (empty($model->company_id) && Auth::check() && isset(Auth::user()->company_id)) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

}
