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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->comment('User who made the movement');
            $table->enum('type', ['initial', 'purchase', 'sale', 'transfer_in', 'transfer_out', 'damage', 'adjustment']);
            $table->integer('quantity')->comment('Positive for addition, Negative for deduction');
            $table->string('reference')->nullable()->comment('Order ID, PO ID, etc.');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
