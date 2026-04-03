<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_ring_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('strategy')->default('simultaneous');
            $table->unsignedInteger('timeout_seconds')->default(25);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('titanhello_ring_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ring_group_id')->index();
            $table->string('label')->nullable();
            $table->string('phone_number');
            $table->unsignedInteger('priority')->default(10);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_ring_group_members');
        Schema::dropIfExists('titanhello_ring_groups');
    }
};
