<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add workspace scoping and agent assignment to chatbot system.
     * This enables multi-tenancy and agent-based conversation routing.
     */
    public function up(): void
    {
        // =====================================================================
        // Add workspace_id to ext_chatbots table
        // =====================================================================
        Schema::table('ext_chatbots', function (Blueprint $table) {
            // Skip if column already exists
            if (!Schema::hasColumn('ext_chatbots', 'workspace_id')) {
                $table->after('user_id', function (Blueprint $table) {
                    $table->foreignId('workspace_id')
                        ->constrained('workspaces')
                        ->cascadeOnDelete()
                        ->comment('Workspace owner of this chatbot');
                });

                $table->index('workspace_id');
                $table->index(['workspace_id', 'user_id']);
            }
        });

        // =====================================================================
        // Add agent assignment and service request linking to conversations
        // =====================================================================
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            // Add assigned agent (null = AI handling)
            if (!Schema::hasColumn('ext_chatbot_conversations', 'assigned_agent_id')) {
                $table->after('chatbot_customer_id', function (Blueprint $table) {
                    $table->foreignId('assigned_agent_id')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete()
                        ->comment('User assigned to handle this conversation');
                });

                $table->index('assigned_agent_id');
            }

            // Link to WorkSuite service request (for escalations)
            if (!Schema::hasColumn('ext_chatbot_conversations', 'service_request_id')) {
                $table->after('assigned_agent_id', function (Blueprint $table) {
                    $table->foreignId('service_request_id')
                        ->nullable()
                        ->constrained('service_requests')
                        ->nullOnDelete()
                        ->comment('Linked service request for escalations');
                });

                $table->index('service_request_id');
            }

            // Add customer read tracking
            if (!Schema::hasColumn('ext_chatbot_conversations', 'customer_read_at')) {
                $table->after('connect_agent_at', function (Blueprint $table) {
                    $table->timestamp('customer_read_at')
                        ->nullable()
                        ->comment('When customer last read messages');
                });
            }

            // Add close tracking
            if (!Schema::hasColumn('ext_chatbot_conversations', 'closed_at')) {
                $table->after('closed', function (Blueprint $table) {
                    $table->timestamp('closed_at')
                        ->nullable()
                        ->comment('When conversation was closed');
                });
            }

            // Create composite index for agent querying
            $table->index(['assigned_agent_id', 'closed', 'last_activity_at']);
            $table->index(['closed', 'created_at']);
        });

        // =====================================================================
        // Add read tracking and interaction metadata to message histories
        // =====================================================================
        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            // Add customer read tracking for agent messages
            if (!Schema::hasColumn('ext_chatbot_histories', 'customer_read_at')) {
                $table->after('created_at', function (Blueprint $table) {
                    $table->timestamp('customer_read_at')
                        ->nullable()
                        ->comment('When customer read this message');
                });
            }

            // Index for unread message queries
            $table->index(['conversation_id', 'role', 'customer_read_at']);
        });

        // =====================================================================
        // Create composite indexes for performance
        // =====================================================================
        Schema::table('ext_chatbots', function (Blueprint $table) {
            // For listing chatbots by workspace
            $table->index(['workspace_id', 'created_at']);
        });

        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            // For agent's conversation list
            $table->index(['assigned_agent_id', 'closed', 'last_activity_at']);
            // For customer's conversation list
            $table->index(['chatbot_customer_id', 'created_at']);
            // For analytics
            $table->index(['chatbot_id', 'closed', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropIndexIfExists(['workspace_id']);
            $table->dropIndexIfExists(['workspace_id', 'user_id']);
            $table->dropIndexIfExists(['workspace_id', 'created_at']);
            $table->dropColumn('workspace_id');
        });

        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropIndexIfExists('ext_chatbot_conversations_assigned_agent_id_index');
            $table->dropIndexIfExists('ext_chatbot_conversations_service_request_id_index');
            $table->dropIndexIfExists(['assigned_agent_id', 'closed', 'last_activity_at']);
            $table->dropIndexIfExists(['closed', 'created_at']);
            $table->dropIndexIfExists(['chatbot_customer_id', 'created_at']);
            $table->dropIndexIfExists(['chatbot_id', 'closed', 'created_at']);
            $table->dropColumn(['assigned_agent_id', 'service_request_id', 'customer_read_at', 'closed_at']);
        });

        Schema::table('ext_chatbot_histories', function (Blueprint $table) {
            $table->dropIndexIfExists(['conversation_id', 'role', 'customer_read_at']);
            $table->dropColumn('customer_read_at');
        });
    }
};
