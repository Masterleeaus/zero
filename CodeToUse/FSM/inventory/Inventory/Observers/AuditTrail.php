<?php

namespace Modules\Inventory\Observers;

use Modules\Inventory\Entities\InventoryAudit;

trait AuditTrail
{
    public static function bootAuditTrail()
    {
        static::created(function($model){
            InventoryAudit::create(['action'=>class_basename($model).'.created','context'=>$model->toArray(),'tenant_id'=>$model->tenant_id ?? null]);
        });
        static::updated(function($model){
            InventoryAudit::create(['action'=>class_basename($model).'.updated','context'=>$model->getChanges(),'tenant_id'=>$model->tenant_id ?? null]);
        });
        static::deleted(function($model){
            InventoryAudit::create(['action'=>class_basename($model).'.deleted','context'=>['id'=>$model->id],'tenant_id'=>$model->tenant_id ?? null]);
        });
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restored(function($model){
                InventoryAudit::create(['action'=>class_basename($model).'.restored','context'=>['id'=>$model->id],'tenant_id'=>$model->tenant_id ?? null]);
            });
        }
    }
}
