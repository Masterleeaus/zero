<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_hello_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 32)->unique();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);

            // take_message | forward
            $table->string('after_hours_mode', 32)->nullable();
            $table->string('forward_number', 32)->nullable();

            // Optional: map a default agent (uses existing ext_voice_chatbots id)
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->index(['agent_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_hello_phone_numbers');
    }
};
