<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ext_chatbot_offline_voice_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('channel', 32)->default('voice');
            $table->text('transcript');
            $table->json('payload')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();
            $table->index(['conversation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_offline_voice_actions');
    }
};
