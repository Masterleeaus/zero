<?php

namespace Modules\Inventory\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Entities\{Item,InventoryAudit};

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creating_item_writes_audit_row()
    {
        Item::create(['name'=>'Audit Demo','qty'=>1]);
        $this->assertDatabaseHas('inventory_audits', ['action'=>'Item.created']);
    }
}
