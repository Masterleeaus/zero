<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbots', 'workspace_id')) {
                $table->unsignedBigInteger('workspace_id')->nullable()->after('user_id');
                $table->index('workspace_id');
            }
            if (! Schema::hasColumn('ext_chatbots', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('workspace_id');
                $table->index('team_id');
            }
            if (! Schema::hasColumn('ext_chatbots', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('team_id');
                $table->index('company_id');
            }
        });

        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbot_conversations', 'assigned_agent_id')) {
                $table->unsignedBigInteger('assigned_agent_id')->nullable()->after('chatbot_customer_id');
                $table->index('assigned_agent_id');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'service_request_id')) {
                $table->unsignedBigInteger('service_request_id')->nullable()->after('assigned_agent_id');
                $table->index('service_request_id');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'customer_read_at')) {
                $table->timestamp('customer_read_at')->nullable()->after('last_activity_at');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('customer_read_at');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'closed')) {
                $table->boolean('closed')->default(false)->after('closed_at');
                $table->index('closed');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('closed');
                $table->index('team_id');
            }
            if (! Schema::hasColumn('ext_chatbot_conversations', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('team_id');
                $table->index('company_id');
            }
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbot_histories', 'customer_read_at')) {
                $table->timestamp('customer_read_at')->nullable()->after('read_at');
            }
        });

        Schema::table('ext_chatbot_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbot_channels', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('chatbot_id');
                $table->index('team_id');
            }
            if (! Schema::hasColumn('ext_chatbot_channels', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('team_id');
                $table->index('company_id');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback intentionally omitted for merge safety.
    }
};
