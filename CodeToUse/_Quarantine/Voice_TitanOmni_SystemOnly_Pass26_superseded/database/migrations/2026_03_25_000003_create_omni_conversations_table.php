<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('agent_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->string('channel_type', 50)->default('web')->index();
            $table->string('channel_id')->nullable()->index();
            $table->string('external_conversation_id')->nullable()->index();
            $table->string('status', 50)->default('open')->index();
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('user_messages')->default(0);
            $table->unsignedInteger('assistant_messages')->default(0);
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_conversations');
    }
};
