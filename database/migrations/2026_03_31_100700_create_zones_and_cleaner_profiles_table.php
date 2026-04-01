<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cleaner_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('zone_id')->nullable()->index();
            $table->string('employment_type')->default('casual');
            $table->date('hire_date')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('zone_id')->references('id')->on('zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaner_profiles');
        Schema::dropIfExists('zones');
    }
};
