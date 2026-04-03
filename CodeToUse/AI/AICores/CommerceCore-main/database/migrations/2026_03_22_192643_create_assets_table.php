<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->date('purchase_date');
            $table->decimal('depreciation_percentage', 5, 2)->default(0);
            $table->enum('status', ['in_use', 'maintenance', 'sold', 'disposed'])->default('in_use');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
