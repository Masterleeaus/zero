<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titan_rewind_events')) {
            return;
        }
        Schema::create('titan_rewind_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id');
            $table->string('event_type', 80);
            $table->string('entity_type', 80)->nullable();
            $table->string('entity_id', 80)->nullable();
            $table->string('actor_type', 30)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('idempotency_key', 120)->nullable();
            $table->json('payload_json')->nullable();
            $table->string('event_hash', 64)->nullable();
            $table->string('prev_event_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['company_id', 'case_id']);
            $table->unique(
                ['company_id', 'case_id', 'idempotency_key'],
                'tr_events_idempotency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_rewind_events');
    }
};
