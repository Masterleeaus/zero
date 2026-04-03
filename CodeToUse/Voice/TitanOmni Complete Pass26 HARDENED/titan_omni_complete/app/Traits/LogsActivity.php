<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * LogsActivity trait enables comprehensive audit logging for Omni models.
 * Tracks all mutations with user context, timestamps, and change diffs.
 *
 * Usage:
 *   class OmniMessage extends Model {
 *       use LogsActivity;
 *       protected $auditLogFields = ['content', 'role', 'is_internal_note'];
 *   }
 */
trait LogsActivity
{
    /**
     * Boot the trait and register event listeners.
     */
    public static function bootLogsActivity(): void
    {
        static::creating(fn (Model $model) => $model->logActivity('create', []));
        static::updating(fn (Model $model) => $model->logActivity('update', $model->getChanges()));
        static::deleting(fn (Model $model) => $model->logActivity('delete', $model->getAttributes()));
    }

    /**
     * Log activity for audit trail.
     */
    protected function logActivity(string $action, array $changes): void
    {
        if (!config('omni.enable_audit_logging', false)) {
            return;
        }

        $table = $this->getTable();
        $fieldsToLog = $this->auditLogFields ?? array_keys($this->getFillable());

        // Filter changes to only logged fields
        $filteredChanges = array_intersect_key($changes, array_flip($fieldsToLog));

        if (empty($filteredChanges) && $action !== 'delete') {
            return;
        }

        \DB::table('omni_audit_logs')->insert([
            'model_type' => static::class,
            'model_id' => $this->getKey(),
            'table' => $table,
            'action' => $action,
            'user_id' => auth()->id(),
            'company_id' => $this->company_id ?? null,
            'old_values' => $action === 'update' ? json_encode($this->getOriginal()) : null,
            'new_values' => json_encode($filteredChanges),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        \Log::info("Activity logged for {$table}", [
            'action' => $action,
            'model_id' => $this->getKey(),
            'user_id' => auth()->id(),
            'changes' => $filteredChanges,
        ]);
    }

    /**
     * Get audit log history for this model.
     */
    public function auditLogs()
    {
        return \DB::table('omni_audit_logs')
            ->where('model_type', static::class)
            ->where('model_id', $this->getKey())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
