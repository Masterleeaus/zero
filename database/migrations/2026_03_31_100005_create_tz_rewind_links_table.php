<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tz_rewind_links')) {
            return;
        }
        Schema::create('tz_rewind_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('case_id')->nullable();
            $table->string('parent_process_id', 80)->nullable();
            $table->string('child_process_id', 80)->nullable();
            $table->string('parent_entity_type', 80)->nullable();
            $table->string('parent_entity_id', 80)->nullable();
            $table->string('child_entity_type', 80)->nullable();
            $table->string('child_entity_id', 80)->nullable();
            $table->string('relationship_type', 50)->default('cascade');
            $table->unsignedInteger('depth')->default(1);
            $table->boolean('can_reuse')->default(true);
            $table->boolean('must_reissue')->default(false);
            $table->string('status', 30)->default('held');
            $table->string('action_required', 30)->nullable();
            $table->text('held_reason')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'parent_process_id']);
            $table->index(['company_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_rewind_links');
    }
};
