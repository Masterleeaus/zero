<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->enum('type', ['in','out','adjust'])->default('in');
                $table->integer('qty_change'); // positive for in, negative for out
                $table->string('ref')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
