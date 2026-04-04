<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ext_chatbot_call_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->string('call_sid', 100)->nullable()->index();
            $table->string('from_number', 30)->nullable()->index();
            $table->string('to_number', 30)->nullable();
            $table->string('call_status', 50)->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('queue_wait_seconds')->default(0);
            $table->unsignedBigInteger('transferred_to_agent_id')->nullable();
            $table->string('recording_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ext_chatbot_callback_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 30)->index();
            $table->timestamp('scheduled_at')->index();
            $table->string('status', 50)->default('scheduled')->index();
            $table->string('call_sid', 100)->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ext_chatbot_business_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->unsignedTinyInteger('opening_hour')->nullable();
            $table->unsignedTinyInteger('closing_hour')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_business_hours');
        Schema::dropIfExists('ext_chatbot_callback_schedules');
        Schema::dropIfExists('ext_chatbot_call_logs');
    }
};
