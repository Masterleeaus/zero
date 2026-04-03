<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ext_chatbot_conversations')) {
            return;
        }

        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbot_conversations', 'site_id')) {
                $table->unsignedBigInteger('site_id')->nullable()->after('chatbot_customer_id');
            }

            if (! Schema::hasColumn('ext_chatbot_conversations', 'workcore_project_id')) {
                $table->unsignedBigInteger('workcore_project_id')->nullable()->after('site_id');
            }

            if (! Schema::hasColumn('ext_chatbot_conversations', 'workcore_invoice_id')) {
                $table->unsignedBigInteger('workcore_invoice_id')->nullable()->after('workcore_project_id');
            }

            if (! Schema::hasColumn('ext_chatbot_conversations', 'workcore_ticket_id')) {
                $table->unsignedBigInteger('workcore_ticket_id')->nullable()->after('workcore_invoice_id');
            }

            if (! Schema::hasColumn('ext_chatbot_conversations', 'portal_context')) {
                $table->json('portal_context')->nullable()->after('customer_payload');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ext_chatbot_conversations')) {
            return;
        }

        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            foreach ([
                'site_id',
                'workcore_project_id',
                'workcore_invoice_id',
                'workcore_ticket_id',
                'portal_context',
            ] as $column) {
                if (Schema::hasColumn('ext_chatbot_conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
