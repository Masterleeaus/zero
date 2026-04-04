<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voice_command_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->text('transcript');
            $table->string('parsed_intent', 100)->index();
            $table->json('entities')->nullable();
            $table->decimal('confidence', 4, 2)->default(0);
            $table->string('status', 50)->default('parsed');
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });

        Schema::create('callback_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('phone_number', 40)->index();
            $table->timestamp('scheduled_at')->index();
            $table->string('status', 50)->default('pending')->index();
            $table->string('call_sid', 100)->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps();
        });

        Schema::create('call_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('call_sid', 100)->index();
            $table->string('from_number', 40)->nullable()->index();
            $table->string('to_number', 40)->nullable();
            $table->string('call_status', 50)->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('queue_wait_seconds')->default(0);
            $table->unsignedBigInteger('transferred_to_agent_id')->nullable();
            $table->string('recording_url')->nullable();
            $table->timestamps();
        });

        Schema::table('chatbot_conversations', function (Blueprint $table): void {
            if (!Schema::hasColumn('chatbot_conversations', 'voice_command_context')) {
                $table->json('voice_command_context')->nullable()->after('session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('callback_schedules');
        Schema::dropIfExists('voice_command_logs');

        Schema::table('chatbot_conversations', function (Blueprint $table): void {
            if (Schema::hasColumn('chatbot_conversations', 'voice_command_context')) {
                $table->dropColumn('voice_command_context');
            }
        });
    }
};
