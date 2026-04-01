<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owner_cannot_see_other_stores_orders()
    {
        // 1. Setup Store A and Store B
        $ownerA = User::factory()->create(['role' => 'store_owner']);
        $storeA = Store::factory()->create(['owner_id' => $ownerA->id]);
        $orderA = Order::factory()->create(['store_id' => $storeA->id, 'total_price' => 100.00]);

        $ownerB = User::factory()->create(['role' => 'store_owner']);
        $storeB = Store::factory()->create(['owner_id' => $ownerB->id]);
        $orderB = Order::factory()->create(['store_id' => $storeB->id, 'total_price' => 100.00]);

        // 2. Action: Owner A tries to access Order B
        $this->actingAs($ownerA);

        // Simulate dashboard context for Owner A
        $this->actingAs($ownerA);
        // Bind the tenant ID as the middleware would
        app()->instance('current_tenant_id', $storeA->id);

        // Ensure that order query is scoped
        $this->assertCount(1, Order::all()); // Should only see Order A due to Global Scope
        $this->assertEquals($orderA->id, Order::first()->id);

        // 3. Negative check: Trying to fetch Order B directly should fail or return 404
        // Assuming we have a route like admin.orders.show
        $response = $this->get(route('admin.orders.show', $orderB));
        
        $response->assertStatus(404);
    }
}
