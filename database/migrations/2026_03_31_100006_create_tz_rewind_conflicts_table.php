<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tz_rewind_conflicts')) {
            return;
        }
        Schema::create('tz_rewind_conflicts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id');
            $table->string('process_id', 80)->nullable();
            $table->string('entity_type', 80)->nullable();
            $table->string('entity_id', 80)->nullable();
            $table->string('conflict_type', 80);
            $table->string('severity', 20)->default('high');
            $table->string('status', 20)->default('open');
            $table->text('message');
            $table->json('details_json')->nullable();
            $table->text('resolution_hint')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_type', 20)->nullable();
            $table->unsignedBigInteger('resolved_by_id')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'case_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_rewind_conflicts');
    }
};
