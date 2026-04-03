<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('channel_conversations') && !Schema::hasColumn('channel_conversations', 'company_id')) {
            Schema::table('channel_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('channel_lists') && !Schema::hasColumn('channel_lists', 'company_id')) {
            Schema::table('channel_lists', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('channel_users') && !Schema::hasColumn('channel_users', 'company_id')) {
            Schema::table('channel_users', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('conversation_files') && !Schema::hasColumn('conversation_files', 'company_id')) {
            Schema::table('conversation_files', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};
