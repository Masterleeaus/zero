<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->index(['team_id', 'closed_at', 'assigned_agent_id'], 'ext_chatbot_conv_team_closed_agent_idx');
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'ext_chatbot_histories_conversation_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->dropIndex('ext_chatbot_conv_team_closed_agent_idx');
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            $table->dropIndex('ext_chatbot_histories_conversation_created_idx');
        });
    }
};
