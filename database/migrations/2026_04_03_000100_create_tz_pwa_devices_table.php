<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_pwa_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('node_id', 64)->unique();
            $table->string('node_origin')->nullable();
            $table->string('trust_level', 20)->default('provisional');
            $table->string('device_label')->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_pwa_devices');
    }
};
