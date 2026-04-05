<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Events\Inventory\MaterialIssuedToJob;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class MaterialUsageService
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * Issue material to a job. Creates a stock movement of type 'issue' and a job_material_usage record.
     */
    public function issueToJob(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItem::withoutGlobalScopes()->findOrFail($data['item_id']);

            $costPerUnit = $data['cost_per_unit'] ?? $item->cost_price;

            $movement = $this->stockService->recordMovement([
                'company_id'      => $data['company_id'],
                'created_by'      => $data['created_by'],
                'item_id'         => $data['item_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'type'            => 'issue',
                'qty_change'      => -abs((int) $data['qty']),
                'reference'       => 'JOB-' . $data['service_job_id'],
                'note'            => $data['note'] ?? null,
                'cost_per_unit'   => $costPerUnit,
                'service_job_id'  => $data['service_job_id'],
                'movement_reason' => 'issue_to_job',
            ]);

            DB::table('job_material_usage')->insert([
                'company_id'        => $data['company_id'],
                'created_by'        => $data['created_by'] ?? null,
                'job_id'            => $data['service_job_id'],
                'service_job_id'    => $data['service_job_id'],
                'item_id'           => $data['item_id'],
                'warehouse_id'      => $data['warehouse_id'],
                'qty_used'          => abs((int) $data['qty']),
                'cost_per_unit'     => $costPerUnit,
                'note'              => $data['note'] ?? null,
                'stock_movement_id' => $movement->id,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            Event::dispatch(new MaterialIssuedToJob(
                $data['company_id'],
                $data['service_job_id'],
                $data['item_id'],
                abs((int) $data['qty']),
                (float) $costPerUnit
            ));

            return $movement;
        });
    }

    /**
     * Transfer stock between warehouses.
     */
    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $ref = 'TRF-' . now()->format('YmdHis');

            $out = $this->stockService->recordMovement([
                'company_id'      => $data['company_id'],
                'created_by'      => $data['created_by'],
                'item_id'         => $data['item_id'],
                'warehouse_id'    => $data['from_warehouse_id'],
                'type'            => 'transfer_out',
                'qty_change'      => -abs((int) $data['qty']),
                'reference'       => $ref,
                'note'            => $data['note'] ?? 'Transfer out',
                'movement_reason' => 'transfer',
            ]);

            $in = $this->stockService->recordMovement([
                'company_id'      => $data['company_id'],
                'created_by'      => $data['created_by'],
                'item_id'         => $data['item_id'],
                'warehouse_id'    => $data['to_warehouse_id'],
                'type'            => 'transfer_in',
                'qty_change'      => abs((int) $data['qty']),
                'reference'       => $ref,
                'note'            => $data['note'] ?? 'Transfer in',
                'movement_reason' => 'transfer',
            ]);

            return ['out' => $out, 'in' => $in];
        });
    }
}
