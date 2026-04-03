<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_submissions', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_submissions');
    }
};
