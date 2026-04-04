<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified Conversation Fields — Phase 6 of Chatbot/AIChatPro/Canvas Brain Merge
 *
 * Adds Titan-standard surface/channel/entity columns to user_openai_chat so that
 * all three surfaces (chatbot, AIChatPro, Canvas) share one canonical conversation
 * model scoped by tenant, surface, and optionally a linked business entity.
 *
 * Additive-only. No existing columns are altered or removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_openai_chat', function (Blueprint $table) {
            if (! Schema::hasColumn('user_openai_chat', 'surface_id')) {
                $table->string('surface_id', 64)->nullable()->after('team_id')
                    ->comment('Which UI surface owns this conversation: aichatpro|canvas|chatbot|workspace');
            }

            if (! Schema::hasColumn('user_openai_chat', 'channel_type')) {
                $table->string('channel_type', 64)->nullable()->after('surface_id')
                    ->comment('Channel adapter: workspace|messenger|whatsapp|telegram|voice|webchat|external');
            }

            if (! Schema::hasColumn('user_openai_chat', 'entity_type')) {
                $table->string('entity_type', 64)->nullable()->after('channel_type')
                    ->comment('Linked business entity type: job|invoice|quote|customer|asset|null');
            }

            if (! Schema::hasColumn('user_openai_chat', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type')
                    ->comment('Linked business entity primary key');
            }

            if (! Schema::hasColumn('user_openai_chat', 'signal_refs')) {
                $table->json('signal_refs')->nullable()->after('entity_id')
                    ->comment('JSON array of Titan signal IDs emitted during this conversation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_openai_chat', function (Blueprint $table) {
            $table->dropColumn(['surface_id', 'channel_type', 'entity_type', 'entity_id', 'signal_refs']);
        });
    }
};
