<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_pwa_signal_ingress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('node_id', 64)->index();
            $table->string('signal_key', 100)->index();
            $table->string('signal_stage', 30)->default('pending');
            $table->json('payload');
            $table->string('signature', 256)->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->float('consensus_score')->default(0.0);
            $table->boolean('consensus_passed')->default(false);
            $table->json('envelope')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('promoted_to_event_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'node_id', 'signal_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_pwa_signal_ingress');
    }
};
