<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workflow_runs')) {
            Schema::create('workflow_runs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('workflow_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->string('event_name', 191)->nullable();
                $table->json('event_payload')->nullable();

                $table->string('status', 30)->default('pending'); // pending,running,done,failed
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('workflow_run_steps')) {
            Schema::create('workflow_run_steps', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('workflow_run_id')->index();
                $table->unsignedBigInteger('workflow_id')->index();
                $table->unsignedInteger('position')->default(1);
                $table->string('type', 100);
                $table->string('handler', 191)->nullable();
                $table->json('config')->nullable();
                $table->string('status', 30)->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: keep history
    }
};
