<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // payment_failure, low_stock, revenue_drop, fraud, api_error
            $table->string('severity')->default('warning'); // info, warning, critical
            $table->string('title');
            $table->text('message');
            $table->text('suggested_action')->nullable();
            $table->string('status')->default('active'); // active, acknowledged, resolved
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};
