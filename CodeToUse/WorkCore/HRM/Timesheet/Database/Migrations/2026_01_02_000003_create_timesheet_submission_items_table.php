<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_submission_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id')->index();
            $table->unsignedBigInteger('timesheet_id')->index();
            $table->timestamps();

            $table->unique(['submission_id', 'timesheet_id'], 'timesheet_submission_items_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_submission_items');
    }
};
