<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titan_rewind_fixes')) {
            return;
        }
        Schema::create('titan_rewind_fixes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id');
            $table->string('fix_type', 80);
            $table->string('proposed_by_type', 20)->nullable();
            $table->unsignedBigInteger('proposed_by_id')->nullable();
            $table->boolean('requires_confirmation')->default(true);
            $table->string('status', 30)->default('proposed');
            $table->json('proposal_json')->nullable();
            $table->string('confirm_token', 80)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_by_type', 20)->nullable();
            $table->unsignedBigInteger('confirmed_by_id')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->string('applied_by_type', 20)->nullable();
            $table->unsignedBigInteger('applied_by_id')->nullable();
            $table->json('result_json')->nullable();
            $table->text('error_text')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'case_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_rewind_fixes');
    }
};
