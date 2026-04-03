<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workflow_logs')) {
            Schema::create('workflow_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('workflow_id')->index();
                $table->unsignedBigInteger('step_id')->nullable()->index();
                $table->string('level', 20)->default('info'); // info, warning, error
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_logs')) {
            Schema::drop('workflow_logs');
        }
    }
};
