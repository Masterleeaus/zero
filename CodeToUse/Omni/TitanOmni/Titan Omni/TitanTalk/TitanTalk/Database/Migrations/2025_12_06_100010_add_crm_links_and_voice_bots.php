<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_converse_conversations')) {
            Schema::table('ai_converse_conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('ai_converse_conversations', 'client_id')) {
                    $table->unsignedBigInteger('client_id')->nullable()->after('context');
                }
                if (!Schema::hasColumn('ai_converse_conversations', 'lead_id')) {
                    $table->unsignedBigInteger('lead_id')->nullable()->after('client_id');
                }
                if (!Schema::hasColumn('ai_converse_conversations', 'project_id')) {
                    $table->unsignedBigInteger('project_id')->nullable()->after('lead_id');
                }
            });
        }

        if (!Schema::hasTable('ai_converse_voice_bots')) {
            Schema::create('ai_converse_voice_bots', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('provider')->default('elevenlabs');
                $table->string('external_id')->nullable(); // e.g. ElevenLabs voice or agent id
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_converse_conversations')) {
            Schema::table('ai_converse_conversations', function (Blueprint $table) {
                if (Schema::hasColumn('ai_converse_conversations', 'client_id')) {
                    $table->dropColumn('client_id');
                }
                if (Schema::hasColumn('ai_converse_conversations', 'lead_id')) {
                    $table->dropColumn('lead_id');
                }
                if (Schema::hasColumn('ai_converse_conversations', 'project_id')) {
                    $table->dropColumn('project_id');
                }
            });
        }

        Schema::dropIfExists('ai_converse_voice_bots');
    }
};
