<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_hello_call_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_session_id');

            $table->string('type', 64); // ringing | answered | transcript | tool_call | transfer | hangup ...
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['call_session_id']);
            $table->index(['type']);

            $table->foreign('call_session_id')
                ->references('id')
                ->on('titan_hello_call_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_hello_call_events');
    }
};
