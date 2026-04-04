<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbot_conversations', 'workspace_id')) {
                $table->unsignedBigInteger('workspace_id')->nullable()->after('company_id')->index();
            }

            $table->index(['workspace_id', 'closed', 'assigned_agent_id'], 'ext_chatbot_conv_workspace_queue_idx');
            $table->index(['chatbot_id', 'chatbot_channel', 'last_activity_at'], 'ext_chatbot_conv_channel_activity_idx');
        });

        Schema::table('ext_chatbot_channels', function (Blueprint $table) {
            $table->index(['chatbot_id', 'channel', 'connected_at'], 'ext_chatbot_channels_bot_channel_idx');
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'ext_chatbot_histories_conversation_created_idx');
            $table->index(['company_id', 'team_id'], 'ext_chatbot_histories_tenant_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->dropIndex('ext_chatbot_conv_workspace_queue_idx');
            $table->dropIndex('ext_chatbot_conv_channel_activity_idx');
        });

        Schema::table('ext_chatbot_channels', function (Blueprint $table) {
            $table->dropIndex('ext_chatbot_channels_bot_channel_idx');
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            $table->dropIndex('ext_chatbot_histories_conversation_created_idx');
            $table->dropIndex('ext_chatbot_histories_tenant_idx');
        });
    }
};
