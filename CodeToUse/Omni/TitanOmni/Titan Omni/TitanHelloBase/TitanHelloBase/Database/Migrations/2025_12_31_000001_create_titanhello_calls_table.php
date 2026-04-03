<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('direction', 20)->default('inbound')->index();
            $table->string('provider', 50)->default('twilio')->index();
            $table->string('provider_call_sid', 128)->nullable()->index();
            $table->string('from_number', 64)->nullable()->index();
            $table->string('to_number', 64)->nullable()->index();
            $table->string('status', 32)->default('ringing')->index();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->boolean('recording_enabled')->default(true);
            $table->unsignedBigInteger('assigned_to_user_id')->nullable()->index();
            $table->string('disposition', 64)->nullable()->index();
            $table->text('disposition_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_calls');
    }
};
