<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('execution_graphs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('graph_id')->unique();
            $table->string('root_subject_type');
            $table->unsignedBigInteger('root_subject_id');
            $table->string('title');
            $table->string('status')->default('active'); // active|completed|rewound|archived
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('event_count')->default(0);
            $table->timestamps();

            $table->index(['root_subject_type', 'root_subject_id']);
        });

        Schema::create('execution_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('graph_id')->index();
            $table->unsignedBigInteger('parent_event_id')->nullable()->index();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('event_class')->nullable();
            $table->string('event_type'); // stage_transition|signal_emitted|user_action|ai_decision|system_trigger|external_event|rewind_applied|sync_received
            $table->string('actor_type')->default('system'); // user|system|ai|external
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('payload')->nullable();
            $table->dateTime('occurred_at', 6); // microsecond precision
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['graph_id', 'sequence']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('execution_graph_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('execution_graph_id')->index();
            $table->unsignedBigInteger('event_id')->nullable()->index();
            $table->string('label');
            $table->json('state_snapshot')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('execution_graph_id')->references('id')->on('execution_graphs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_graph_checkpoints');
        Schema::dropIfExists('execution_events');
        Schema::dropIfExists('execution_graphs');
    }
};
