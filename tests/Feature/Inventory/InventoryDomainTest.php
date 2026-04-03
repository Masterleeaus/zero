<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItem;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Services\Inventory\PurchaseOrderService;
use App\Services\Inventory\StockService;
use App\Services\Inventory\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryDomainTest extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 99;

    private function makeUser(): User
    {
        return User::factory()->create(['company_id' => $this->companyId]);
    }

    public function test_supplier_service_creates_supplier(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $service = app(SupplierService::class);

        $supplier = $service->createSupplier([
            'name'   => 'Acme Supplies',
            'email'  => 'orders@acme.com',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertEquals('Acme Supplies', $supplier->name);
        $this->assertEquals($this->companyId, $supplier->company_id);
    }

    public function test_stock_service_records_movement(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $item = InventoryItem::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Widget A',
            'qty_on_hand' => 0,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Main Warehouse',
        ]);

        $service = app(StockService::class);

        $movement = $service->recordMovement([
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'item_id'      => $item->id,
            'warehouse_id' => $warehouse->id,
            'type'         => 'in',
            'qty_change'   => 50,
            'reference'    => 'TEST-001',
        ]);

        $this->assertEquals(50, $movement->qty_change);
        $this->assertEquals('in', $movement->type);

        $item->refresh();
        $this->assertEquals(50, $item->qty_on_hand);
    }

    public function test_stock_service_on_hand_calculation(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $item = InventoryItem::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'name'        => 'Bolt Pack',
            'qty_on_hand' => 0,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Store B',
        ]);

        $service = app(StockService::class);

        $service->recordMovement([
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'item_id'      => $item->id,
            'warehouse_id' => $warehouse->id,
            'type'         => 'in',
            'qty_change'   => 100,
        ]);

        $service->recordMovement([
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'item_id'      => $item->id,
            'warehouse_id' => $warehouse->id,
            'type'         => 'out',
            'qty_change'   => -30,
        ]);

        $onHand = $service->onHand($item->id, $warehouse->id);
        $this->assertEquals(70, $onHand);
    }

    public function test_purchase_order_service_creates_purchase_order(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Test Vendor',
        ]);

        $service = app(PurchaseOrderService::class);

        $po = $service->createPurchaseOrder(
            [
                'company_id'  => $this->companyId,
                'created_by'  => $user->id,
                'supplier_id' => $supplier->id,
                'order_date'  => now()->format('Y-m-d'),
            ],
            [
                [
                    'description' => 'Widget A x10',
                    'qty_ordered' => 10,
                    'unit_price'  => 5.00,
                    'tax_rate'    => 0,
                ],
            ]
        );

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertStringStartsWith('PO-', $po->po_number);
        $this->assertEquals($this->companyId, $po->company_id);
        $this->assertEquals(50.00, (float) $po->subtotal);
    }

    public function test_purchase_order_receive_creates_stock_movements(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $supplier = Supplier::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Stock Vendor',
        ]);

        $item = InventoryItem::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'name'        => 'Component X',
            'qty_on_hand' => 0,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->companyId,
            'created_by' => $user->id,
            'name'       => 'Receiving Dock',
        ]);

        $service = app(PurchaseOrderService::class);

        $po = $service->createPurchaseOrder(
            [
                'company_id'  => $this->companyId,
                'created_by'  => $user->id,
                'supplier_id' => $supplier->id,
            ],
            [
                [
                    'item_id'     => $item->id,
                    'description' => 'Component X',
                    'qty_ordered' => 20,
                    'unit_price'  => 2.50,
                    'tax_rate'    => 0,
                ],
            ]
        );

        $line = $po->items->first();

        $service->receivePurchaseOrder($po, [
            [
                'id'            => $line->id,
                'qty_receiving' => 20,
                'warehouse_id'  => $warehouse->id,
            ],
        ]);

        $item->refresh();
        $this->assertEquals(20, $item->qty_on_hand);

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    public function test_company_scoping_isolates_inventory(): void
    {
        $userA = User::factory()->create(['company_id' => 101]);
        $userB = User::factory()->create(['company_id' => 102]);

        $this->actingAs($userA);
        InventoryItem::create([
            'company_id' => 101,
            'created_by' => $userA->id,
            'name'       => 'Company A Item',
        ]);

        $this->actingAs($userB);
        $items = InventoryItem::all();
        $this->assertTrue($items->where('name', 'Company A Item')->isEmpty());
    }
}
