<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_dial_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('from_number')->nullable();
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedInteger('retry_minutes')->default(60);
            $table->string('status')->default('draft');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('titanhello_dial_campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->string('name')->nullable();
            $table->string('phone_number')->index();
            $table->json('meta')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_dial_campaign_contacts');
        Schema::dropIfExists('titanhello_dial_campaigns');
    }
};
