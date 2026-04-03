<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

class InventoryAudit extends Model
{
    protected $table = 'inventory_audits';
    protected $guarded = [];
    protected $casts = ['context'=>'array'];
}
