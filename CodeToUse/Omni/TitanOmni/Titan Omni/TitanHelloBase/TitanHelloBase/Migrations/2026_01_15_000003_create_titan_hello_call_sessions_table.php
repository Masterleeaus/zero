<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_hello_call_sessions', function (Blueprint $table) {
            $table->id();

            // Telephony provider primary id (Twilio CallSid)
            $table->string('call_sid', 128)->unique();

            $table->string('from_number', 32)->nullable();
            $table->string('to_number', 32)->nullable();
            $table->string('direction', 32)->nullable();
            $table->string('status', 32)->default('ringing');

            $table->unsignedBigInteger('phone_number_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable(); // maps to ext_voice_chatbots

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->boolean('recording_enabled')->default(false);
            $table->string('recording_url')->nullable();

            $table->text('summary')->nullable();
            $table->json('meta')->nullable();

            $table->index(['phone_number_id']);
            $table->index(['agent_id']);
            $table->index(['status']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_hello_call_sessions');
    }
};
