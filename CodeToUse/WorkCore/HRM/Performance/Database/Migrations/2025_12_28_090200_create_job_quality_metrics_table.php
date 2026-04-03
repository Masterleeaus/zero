<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_quality_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('snapshot_id')->index();
            $table->string('metric_key', 64);
            $table->string('label', 191);
            $table->decimal('value', 8, 2)->nullable();
            $table->string('unit', 32)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_id', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_quality_metrics');
    }
};
