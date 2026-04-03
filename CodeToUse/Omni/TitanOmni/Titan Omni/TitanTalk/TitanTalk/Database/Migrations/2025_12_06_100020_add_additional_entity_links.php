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
                if (! Schema::hasColumn('ai_converse_conversations', 'ticket_id')) {
                    $table->unsignedBigInteger('ticket_id')->nullable()->after('project_id');
                }
                if (! Schema::hasColumn('ai_converse_conversations', 'task_id')) {
                    $table->unsignedBigInteger('task_id')->nullable()->after('ticket_id');
                }
                if (! Schema::hasColumn('ai_converse_conversations', 'invoice_id')) {
                    $table->unsignedBigInteger('invoice_id')->nullable()->after('task_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_converse_conversations')) {
            Schema::table('ai_converse_conversations', function (Blueprint $table) {
                if (Schema::hasColumn('ai_converse_conversations', 'ticket_id')) {
                    $table->dropColumn('ticket_id');
                }
                if (Schema::hasColumn('ai_converse_conversations', 'task_id')) {
                    $table->dropColumn('task_id');
                }
                if (Schema::hasColumn('ai_converse_conversations', 'invoice_id')) {
                    $table->dropColumn('invoice_id');
                }
            });
        }
    }
};
