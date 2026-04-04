<?php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logAction($model, 'created', $model->getAttributes());
        });

        static::updated(function ($model) {
            // Only log the attributes that actually changed
            self::logAction($model, 'updated', $model->getChanges());
        });

        static::deleted(function ($model) {
            self::logAction($model, 'deleted', $model->getAttributes());
        });
    }

    protected static function logAction($model, $action, $changes = null)
    {
        try {
            ActionLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'changes' => $changes,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging fails to prevent breaking core app flows
        }
    }
}
