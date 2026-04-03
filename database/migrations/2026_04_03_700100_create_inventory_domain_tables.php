<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->string('tax_number')->nullable();
                $table->string('payment_terms')->nullable();
                $table->string('currency_code', 10)->default('USD');
                $table->text('notes')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('name');
                $table->string('sku')->nullable();
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->decimal('unit_price', 12, 4)->default(0);
                $table->decimal('cost_price', 12, 4)->default(0);
                $table->integer('qty_on_hand')->default(0);
                $table->integer('reorder_point')->default(0);
                $table->string('unit')->nullable();
                $table->boolean('track_quantity')->default(true);
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
                $table->string('type'); // in|out|adjust|transfer
                $table->integer('qty_change');
                $table->string('reference')->nullable();
                $table->text('note')->nullable();
                $table->nullableMorphs('moveable');
                $table->decimal('cost_per_unit', 12, 4)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('stocktakes')) {
            Schema::create('stocktakes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('ref')->nullable();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->string('status')->default('draft'); // draft|in_progress|final
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('stocktake_lines')) {
            Schema::create('stocktake_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stocktake_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->integer('expected_qty')->default(0);
                $table->integer('counted_qty')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('stocktake_id')->references('id')->on('stocktakes')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('po_number');
                $table->unsignedBigInteger('supplier_id')->index();
                $table->string('status')->default('draft'); // draft|sent|partial|received|cancelled
                $table->date('order_date')->nullable();
                $table->date('expected_date')->nullable();
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('currency_code', 10)->default('USD');
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['company_id', 'po_number']);
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            });
        }

        if (! Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->string('description')->nullable();
                $table->integer('qty_ordered')->default(0);
                $table->integer('qty_received')->default(0);
                $table->decimal('unit_price', 12, 4)->default(0);
                $table->decimal('tax_rate', 8, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('inventory_audits')) {
            Schema::create('inventory_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('action');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('context')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
            });
        }

        if (! Schema::hasTable('job_material_usage')) {
            Schema::create('job_material_usage', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->integer('qty_used')->default(1);
                $table->decimal('cost_per_unit', 12, 4)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            });
        }

        // Add inventory_item_id to equipment if table exists and column missing
        if (Schema::hasTable('equipment') && ! Schema::hasColumn('equipment', 'inventory_item_id')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->unsignedBigInteger('inventory_item_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipment') && Schema::hasColumn('equipment', 'inventory_item_id')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('inventory_item_id');
            });
        }

        Schema::dropIfExists('job_material_usage');
        Schema::dropIfExists('inventory_audits');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stocktake_lines');
        Schema::dropIfExists('stocktakes');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
    }
};
