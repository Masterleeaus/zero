<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tz_ai_memories')) {
            return;
        }

        Schema::create('tz_ai_memories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 160);
            $table->string('type', 80);
            $table->text('content');
            $table->string('embedding_reference', 160)->nullable();
            $table->decimal('importance_score', 5, 4)->default(0.5000);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'session_id'], 'tz_ai_memories_company_session_idx');
            $table->index(['company_id', 'type'], 'tz_ai_memories_company_type_idx');
            $table->index(['company_id', 'importance_score'], 'tz_ai_memories_company_importance_idx');
            $table->index(['company_id', 'expires_at'], 'tz_ai_memories_company_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_ai_memories');
    }
};
