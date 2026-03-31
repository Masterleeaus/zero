<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titan_rewind_actions')) {
            return;
        }
        Schema::create('titan_rewind_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('fix_id')->nullable();
            $table->string('action_type', 80);
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 80)->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('executed_by_type', 20)->nullable();
            $table->unsignedBigInteger('executed_by_id')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_text')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_rewind_actions');
    }
};
