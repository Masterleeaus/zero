<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workflow_steps')) {
            Schema::create('workflow_steps', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('workflow_id')->index();
                $table->unsignedInteger('position')->default(1);
                $table->string('type', 100); // action, approval, notify, webook, ai, custom
                $table->string('handler', 191)->nullable(); // FQCN of handler
                $table->json('config')->nullable();
                $table->string('status', 30)->default('pending'); // pending, running, done, failed, skipped
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_steps')) {
            Schema::drop('workflow_steps');
        }
    }
};
