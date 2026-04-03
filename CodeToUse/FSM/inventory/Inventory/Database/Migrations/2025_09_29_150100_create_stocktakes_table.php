<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('stocktakes')) {
            Schema::create('stocktakes', function (Blueprint $table) {
                $table->id();
                $table->string('ref')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->enum('status',['draft','final'])->default('draft');
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('stocktake_lines')) {
            Schema::create('stocktake_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stocktake_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->integer('counted_qty')->default(0);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('inventory_audits')) {
            Schema::create('inventory_audits', function (Blueprint $table) {
                $table->id();
                $table->string('action'); // create_item, move_stock, stocktake_finalize, etc.
                $table->json('context')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('inventory_audits');
        Schema::dropIfExists('stocktake_lines');
        Schema::dropIfExists('stocktakes');
    }
};
