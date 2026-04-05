<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Events\Inventory\InventoryLowStockDetected;
use App\Events\Inventory\StockVarianceDetected;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItem;
use App\Models\Inventory\Stocktake;
use App\Models\Inventory\StocktakeLine;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Money\SupplierBill;
use App\Models\User;
use App\Services\Inventory\MaterialUsageService;
use App\Services\Inventory\ReorderRecommendationService;
use App\Services\Inventory\ReorderSignalService;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InventoryPhase2Test extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 42;

    private function makeUser(): User
    {
        return User::factory()->create(['company_id' => $this->companyId]);
    }

    private function makeWarehouse(): Warehouse
    {
        return Warehouse::create([
            'company_id' => $this->companyId,
            'name'       => 'Main WH',
            'status'     => 'active',
        ]);
    }

    private function makeItem(array $overrides = []): InventoryItem
    {
        return InventoryItem::create(array_merge([
            'company_id'     => $this->companyId,
            'name'           => 'Test Widget',
            'sku'            => 'WID-' . uniqid(),
            'qty_on_hand'    => 5,
            'reorder_point'  => 10,
            'reorder_qty'    => 20,
            'min_stock'      => 3,
            'cost_price'     => 9.99,
            'track_quantity' => true,
            'status'         => 'active',
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function test_reorder_recommendation_service_returns_low_stock_items(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $this->makeItem(['qty_on_hand' => 2, 'reorder_point' => 10]);

        $service = app(ReorderRecommendationService::class);
        $recs    = $service->generateRecommendations($this->companyId);

        $this->assertNotEmpty($recs);
        $this->assertEquals('Test Widget', $recs[0]['item_name']);
        $this->assertEquals('create_purchase_order', $recs[0]['action']);
    }

    public function test_reorder_signal_service_detects_low_stock_and_emits_event(): void
    {
        Event::fake([InventoryLowStockDetected::class]);

        $user = $this->makeUser();
        $this->actingAs($user);

        $this->makeItem(['qty_on_hand' => 0, 'reorder_point' => 5]);

        $service = app(ReorderSignalService::class);
        $signals = $service->detectLowStock($this->companyId);

        $this->assertNotEmpty($signals);
        $this->assertEquals('critical', $signals[0]['severity']);
        Event::assertDispatched(InventoryLowStockDetected::class);
    }

    public function test_stock_variance_detected_on_stocktake_finalize(): void
    {
        Event::fake([StockVarianceDetected::class]);

        $user      = $this->makeUser();
        $this->actingAs($user);
        $warehouse = $this->makeWarehouse();
        $item      = $this->makeItem(['qty_on_hand' => 10]);

        $stocktake = Stocktake::create([
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'warehouse_id' => $warehouse->id,
            'ref'          => 'ST-TEST-001',
            'status'       => 'in_progress',
        ]);

        StocktakeLine::create([
            'stocktake_id' => $stocktake->id,
            'item_id'      => $item->id,
            'expected_qty' => 10,
            'counted_qty'  => 8, // variance of -2
        ]);

        $signalService = app(ReorderSignalService::class);
        $variances     = $signalService->detectVariances($stocktake->fresh(['lines.item']));

        $this->assertNotEmpty($variances);
        $this->assertEquals(-2, $variances[0]['variance']);
        Event::assertDispatched(StockVarianceDetected::class);
    }

    public function test_stocktake_finalize_is_idempotent(): void
    {
        $user      = $this->makeUser();
        $this->actingAs($user);
        $warehouse = $this->makeWarehouse();

        $stocktake = Stocktake::create([
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'warehouse_id' => $warehouse->id,
            'status'       => 'final',
        ]);

        $response = $this->postJson(
            route('dashboard.inventory.stocktakes.finalize', $stocktake)
        );

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Stocktake already finalized.']);
    }

    public function test_material_usage_service_issues_to_job(): void
    {
        $user      = $this->makeUser();
        $this->actingAs($user);
        $warehouse = $this->makeWarehouse();
        $item      = $this->makeItem(['qty_on_hand' => 20]);

        $service  = app(MaterialUsageService::class);
        $movement = $service->issueToJob([
            'company_id'     => $this->companyId,
            'created_by'     => $user->id,
            'item_id'        => $item->id,
            'warehouse_id'   => $warehouse->id,
            'service_job_id' => 100,
            'qty'            => 3,
            'note'           => 'Used on job',
        ]);

        $this->assertNotNull($movement);
        $this->assertEquals('issue', $movement->type);
        $this->assertEquals(-3, $movement->qty_change);

        $this->assertDatabaseHas('job_material_usage', [
            'service_job_id' => 100,
            'item_id'        => $item->id,
            'qty_used'       => 3,
        ]);

        $item->refresh();
        $this->assertEquals(17, $item->qty_on_hand);
    }

    public function test_ap_bridge_creates_supplier_bill_from_po(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Test Supplier',
        ]);

        $po = PurchaseOrder::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'po_number'   => 'PO-2026-001',
            'supplier_id' => $supplier->id,
            'status'      => 'received',
            'subtotal'    => 100.00,
            'tax_amount'  => 10.00,
            'total_amount' => 110.00,
        ]);

        $item = $this->makeItem();

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id'           => $item->id,
            'description'       => 'Widget',
            'qty_ordered'       => 5,
            'qty_received'      => 5,
            'unit_price'        => 20.00,
            'line_total'        => 100.00,
        ]);

        $service = app(SupplierBillService::class);
        $bill    = $service->createFromPurchaseOrder($po);

        $this->assertInstanceOf(SupplierBill::class, $bill);
        $this->assertEquals($po->id, $bill->purchase_order_id);
        $this->assertEquals('draft', $bill->status);

        // Idempotency: calling again returns same bill
        $bill2 = $service->createFromPurchaseOrder($po);
        $this->assertEquals($bill->id, $bill2->id);
    }

    public function test_low_stock_flag_updated_by_signal_service(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $item = $this->makeItem(['qty_on_hand' => 0, 'reorder_point' => 10, 'low_stock_flag' => false]);

        $service = app(ReorderSignalService::class);
        $service->detectLowStock($this->companyId);

        $item->refresh();
        $this->assertTrue((bool) $item->low_stock_flag);
    }

    public function test_company_scoping_on_reorder_recommendations(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        // Item in different company — should not appear
        InventoryItem::create([
            'company_id'     => 999,
            'name'           => 'Other Co Item',
            'sku'            => 'OCI-001',
            'qty_on_hand'    => 0,
            'reorder_point'  => 10,
            'track_quantity' => true,
            'status'         => 'active',
        ]);

        // Item in our company
        $this->makeItem(['qty_on_hand' => 1, 'reorder_point' => 10]);

        $service = app(ReorderRecommendationService::class);
        $recs    = $service->generateRecommendations($this->companyId);

        $this->assertCount(1, $recs);
        foreach ($recs as $rec) {
            // Verify by checking the item belongs to our company
            $dbItem = InventoryItem::withoutGlobalScopes()->find($rec['item_id']);
            $this->assertEquals($this->companyId, $dbItem->company_id);
        }
    }
}
