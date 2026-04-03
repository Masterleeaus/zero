<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_channel_bridges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('agent_id')->index();
            $table->string('channel', 50)->index();
            $table->string('bridge_driver')->nullable();
            $table->string('bridge_key')->nullable();
            $table->text('bridge_secret')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_channel_bridges');
    }
};
