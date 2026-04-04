<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('message_type', 50)->default('text')->index();
            $table->longText('content')->nullable();
            $table->string('role', 50)->default('user')->index();
            $table->string('voice_file_url')->nullable();
            $table->unsignedInteger('voice_duration_seconds')->nullable();
            $table->string('voice_model')->nullable();
            $table->longText('voice_transcript')->nullable();
            $table->float('voice_confidence')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->unsignedBigInteger('media_size_bytes')->nullable();
            $table->string('external_message_id')->nullable()->index();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_internal_note')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_messages');
    }
};
