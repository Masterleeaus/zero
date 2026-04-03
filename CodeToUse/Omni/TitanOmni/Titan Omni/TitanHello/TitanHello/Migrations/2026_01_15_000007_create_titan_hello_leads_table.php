<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_hello_leads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('call_session_id')->unique();
            $table->string('caller_name', 128)->nullable();
            $table->string('caller_phone', 32)->nullable();
            $table->string('suburb', 128)->nullable();
            $table->string('job_type', 128)->nullable();
            $table->string('urgency', 32)->nullable();
            $table->string('callback_window', 64)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['urgency']);
            $table->index(['job_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_hello_leads');
    }
};
