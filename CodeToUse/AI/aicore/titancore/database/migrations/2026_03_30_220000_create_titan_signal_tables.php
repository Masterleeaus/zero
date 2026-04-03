<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tz_processes')) {
            Schema::create('tz_processes', static function (Blueprint $table) {
                $table->string('id', 80)->primary();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('entity_type', 80);
                $table->string('domain', 80);
                $table->string('originating_node', 80)->nullable();
                $table->string('current_state', 80)->default('initiated');
                $table->json('data')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'current_state'], 'idx_tz_processes_company_state');
                $table->index(['company_id', 'domain'], 'idx_tz_processes_company_domain');
            });
        }

        if (! Schema::hasTable('tz_process_states')) {
            Schema::create('tz_process_states', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('process_id', 80);
                $table->string('from_state', 80)->nullable();
                $table->string('to_state', 80);
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['process_id', 'created_at'], 'idx_tz_process_states_process');
            });
        }

        if (! Schema::hasTable('tz_signals')) {
            Schema::create('tz_signals', static function (Blueprint $table) {
                $table->string('id', 80)->primary();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('process_id', 80)->nullable()->index();
                $table->string('type', 120);
                $table->string('kind', 120);
                $table->string('severity', 20)->default('AMBER');
                $table->string('source', 120)->nullable();
                $table->string('origin', 120)->nullable();
                $table->string('status', 40)->default('new');
                $table->string('validation_result', 40)->nullable();
                $table->json('payload');
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'created_at'], 'idx_tz_signals_company_created');
                $table->index(['company_id', 'type', 'created_at'], 'idx_tz_signals_company_type');
                $table->index(['company_id', 'status', 'created_at'], 'idx_tz_signals_company_status');
            });
        }

        if (! Schema::hasTable('tz_signal_queue')) {
            Schema::create('tz_signal_queue', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('signal_id', 80);
                $table->json('payload');
                $table->timestamp('broadcast_at')->nullable();
                $table->string('broadcast_status', 32)->default('pending');
                $table->integer('retry_count')->default(0);
                $table->json('last_error')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->unique('signal_id', 'uniq_tz_signal_queue_signal');
                $table->index(['broadcast_status', 'created_at'], 'idx_tz_signal_queue_status');
            });
        }

        if (! Schema::hasTable('tz_approval_queue')) {
            Schema::create('tz_approval_queue', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('process_id', 80);
                $table->string('signal_id', 80)->nullable();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->json('approval_chain');
                $table->json('approved_by')->nullable();
                $table->string('current_approver', 120)->nullable();
                $table->string('status', 32)->default('pending');
                $table->json('decision_meta')->nullable();
                $table->timestamps();
                $table->timestamp('decided_at')->nullable();
                $table->unique('process_id', 'uniq_tz_approval_queue_process');
                $table->index(['company_id', 'status', 'created_at'], 'idx_tz_approval_queue_company_status');
            });
        }

        if (! Schema::hasTable('tz_audit_log')) {
            Schema::create('tz_audit_log', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('process_id', 80);
                $table->string('signal_id', 80)->nullable();
                $table->string('action', 80);
                $table->unsignedBigInteger('performed_by')->nullable()->index();
                $table->json('details')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['process_id', 'created_at'], 'idx_tz_audit_log_process');
                $table->index(['action', 'created_at'], 'idx_tz_audit_log_action');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_audit_log');
        Schema::dropIfExists('tz_approval_queue');
        Schema::dropIfExists('tz_signal_queue');
        Schema::dropIfExists('tz_signals');
        Schema::dropIfExists('tz_process_states');
        Schema::dropIfExists('tz_processes');
    }
};
