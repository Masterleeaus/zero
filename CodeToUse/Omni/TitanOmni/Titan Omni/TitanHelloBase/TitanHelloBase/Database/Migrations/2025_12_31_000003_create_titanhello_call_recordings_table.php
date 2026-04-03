<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_call_recordings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('call_id')->index();
            $table->string('provider', 50)->default('twilio')->index();
            $table->string('provider_recording_sid', 128)->nullable()->index();
            $table->text('recording_url')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('content_type', 100)->nullable();
            $table->string('stored_path', 255)->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamps();

            $table->foreign('call_id')->references('id')->on('titanhello_calls')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_call_recordings');
    }
};
