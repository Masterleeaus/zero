<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titan_rewind_cases')) {
            return;
        }
        Schema::create('titan_rewind_cases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('status', 30)->default('open');
            $table->string('severity', 30)->default('medium');
            $table->string('source_type', 80)->nullable();
            $table->string('source_id', 80)->nullable();
            $table->string('process_id', 80)->nullable();
            $table->string('correction_process_id', 80)->nullable();
            $table->string('replacement_process_id', 80)->nullable();
            $table->string('entity_type', 80)->nullable();
            $table->string('entity_id', 80)->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_type', 20)->nullable();
            $table->unsignedBigInteger('resolved_by_id')->nullable();
            $table->timestamp('rollback_completed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'process_id']);
            $table->index(['company_id', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_rewind_cases');
    }
};
