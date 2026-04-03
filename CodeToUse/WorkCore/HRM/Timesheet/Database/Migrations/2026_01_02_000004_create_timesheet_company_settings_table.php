<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_company_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('key', 120);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key'], 'timesheet_company_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_company_settings');
    }
};
