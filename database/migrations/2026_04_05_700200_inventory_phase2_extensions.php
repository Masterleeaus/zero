<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── inventory_items ──────────────────────────────────────────────────
        Schema::table('inventory_items', static function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'reorder_qty')) {
                $table->integer('reorder_qty')->default(0)->after('reorder_point');
            }
            if (! Schema::hasColumn('inventory_items', 'min_stock')) {
                $table->integer('min_stock')->default(0)->after('reorder_qty');
            }
            if (! Schema::hasColumn('inventory_items', 'preferred_supplier_id')) {
                $table->unsignedBigInteger('preferred_supplier_id')->nullable()->after('min_stock');
                $table->foreign('preferred_supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventory_items', 'low_stock_flag')) {
                $table->boolean('low_stock_flag')->default(false)->after('preferred_supplier_id');
            }
        });

        // ── stocktakes ───────────────────────────────────────────────────────
        Schema::table('stocktakes', static function (Blueprint $table) {
            if (! Schema::hasColumn('stocktakes', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('stocktakes', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('finalized_by');
            }
            if (! Schema::hasColumn('stocktakes', 'adjustment_reason')) {
                $table->string('adjustment_reason')->nullable()->after('finalized_at');
            }
        });

        // ── stocktake_lines ──────────────────────────────────────────────────
        Schema::table('stocktake_lines', static function (Blueprint $table) {
            if (! Schema::hasColumn('stocktake_lines', 'variance')) {
                $table->integer('variance')->default(0)->after('counted_qty');
            }
            if (! Schema::hasColumn('stocktake_lines', 'note')) {
                $table->text('note')->nullable()->after('variance');
            }
        });

        // ── purchase_orders ──────────────────────────────────────────────────
        Schema::table('purchase_orders', static function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('purchase_orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
            if (! Schema::hasColumn('purchase_orders', 'receiving_notes')) {
                $table->text('receiving_notes')->nullable()->after('received_at');
            }
        });

        // ── stock_movements ──────────────────────────────────────────────────
        Schema::table('stock_movements', static function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'service_job_id')) {
                $table->unsignedBigInteger('service_job_id')->nullable()->after('purchase_order_id')->index();
            }
            if (! Schema::hasColumn('stock_movements', 'movement_reason')) {
                $table->string('movement_reason')->nullable()->after('service_job_id');
            }
        });

        // ── job_material_usage ───────────────────────────────────────────────
        Schema::table('job_material_usage', static function (Blueprint $table) {
            if (! Schema::hasColumn('job_material_usage', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('job_material_usage', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('company_id');
            }
            if (! Schema::hasColumn('job_material_usage', 'service_job_id')) {
                $table->unsignedBigInteger('service_job_id')->nullable()->after('job_id')->index();
            }
            if (! Schema::hasColumn('job_material_usage', 'stock_movement_id')) {
                $table->unsignedBigInteger('stock_movement_id')->nullable()->after('note')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', static function (Blueprint $table) {
            if (Schema::hasColumn('inventory_items', 'preferred_supplier_id')) {
                $table->dropForeign(['preferred_supplier_id']);
            }
            $columns = array_filter(
                ['reorder_qty', 'min_stock', 'preferred_supplier_id', 'low_stock_flag'],
                fn ($col) => Schema::hasColumn('inventory_items', $col)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('stocktakes', static function (Blueprint $table) {
            $columns = array_filter(
                ['finalized_by', 'finalized_at', 'adjustment_reason'],
                fn ($col) => Schema::hasColumn('stocktakes', $col)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('stocktake_lines', static function (Blueprint $table) {
            $columns = array_filter(
                ['variance', 'note'],
                fn ($col) => Schema::hasColumn('stocktake_lines', $col)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('purchase_orders', static function (Blueprint $table) {
            $columns = array_filter(
                ['received_by', 'received_at', 'receiving_notes'],
                fn ($col) => Schema::hasColumn('purchase_orders', $col)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('stock_movements', static function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'service_job_id')) {
                $table->dropIndex(['service_job_id']);
                $table->dropColumn('service_job_id');
            }
            if (Schema::hasColumn('stock_movements', 'movement_reason')) {
                $table->dropColumn('movement_reason');
            }
        });

        Schema::table('job_material_usage', static function (Blueprint $table) {
            foreach (['company_id', 'service_job_id', 'stock_movement_id'] as $col) {
                if (Schema::hasColumn('job_material_usage', $col)) {
                    $table->dropIndex([$col]);
                }
            }
            $columns = array_filter(
                ['company_id', 'created_by', 'service_job_id', 'stock_movement_id'],
                fn ($col) => Schema::hasColumn('job_material_usage', $col)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
