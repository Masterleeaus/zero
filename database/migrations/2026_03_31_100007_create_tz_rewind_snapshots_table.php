<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tz_rewind_snapshots')) {
            return;
        }
        Schema::create('tz_rewind_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id');
            $table->string('snapshot_key', 160);
            $table->string('snapshot_stage', 40)->default('before');
            $table->string('snapshot_scope', 40)->default('entity');
            $table->string('process_id', 80)->nullable();
            $table->string('entity_type', 80)->nullable();
            $table->string('entity_id', 80)->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('source_table', 120)->nullable();
            $table->string('source_pk', 120)->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['company_id', 'case_id', 'snapshot_key'],
                'tz_rewind_snapshots_case_key_unique'
            );
            $table->index(['company_id', 'case_id', 'snapshot_stage']);
            $table->index(['company_id', 'entity_type', 'entity_id']);
            $table->index(['company_id', 'process_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_rewind_snapshots');
    }
};
