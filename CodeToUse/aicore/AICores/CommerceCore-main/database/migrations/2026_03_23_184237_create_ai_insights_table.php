<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('engine'); // sales_ai, inventory_ai, fraud_ai
            $table->string('type'); // revenue_drop, low_stock_prediction, vip_segment, fraud_flag
            $table->string('title');
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->json('data')->nullable(); // Structured data (charts, numbers, etc.)
            $table->string('status')->default('new'); // new, seen, acted_on, dismissed
            $table->decimal('confidence', 5, 2)->nullable(); // AI confidence score 0-100
            $table->timestamps();

            $table->index(['store_id', 'engine']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
