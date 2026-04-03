<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('employee_number')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('pay_frequency')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
