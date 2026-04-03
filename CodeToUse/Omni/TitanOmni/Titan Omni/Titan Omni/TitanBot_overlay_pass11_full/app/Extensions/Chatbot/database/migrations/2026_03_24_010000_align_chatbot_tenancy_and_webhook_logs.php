<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'ext_chatbot_channel_webhooks',
            'ext_chatbot_channels',
            'ext_chatbot_histories',
            'ext_chatbot_customers',
            'ext_chatbot_page_visits',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'team_id')) {
                    $table->unsignedBigInteger('team_id')->nullable()->index();
                }

                if (! Schema::hasColumn($tableName, 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        // non-destructive alignment migration
    }
};
