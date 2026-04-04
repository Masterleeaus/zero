<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->date('metric_date')->index();
            $table->string('channel', 50)->nullable()->index();
            $table->string('metric_key')->index();
            $table->decimal('metric_value', 14, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_analytics');
    }
};
