<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_inbound_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('phone_number')->index();
            $table->string('label')->nullable();
            $table->string('mode')->default('ring_group');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('business_hours_only')->default(false);
            $table->string('after_hours_mode')->nullable();
            $table->unsignedBigInteger('after_hours_target_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_inbound_numbers');
    }
};
