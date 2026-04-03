<?php

namespace Modules\Inventory\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_warehouses()
    {
        $this->withoutExceptionHandling();
        $resp = $this->getJson('/api/inventory/warehouses');
        $resp->assertStatus(200);
    }
}
