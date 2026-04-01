<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_media_accounts') && ! Schema::hasTable('business_suite_accounts')) {
            Schema::rename('social_media_accounts', 'business_suite_accounts');
        }

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'social_media_agent_limits')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->renameColumn('social_media_agent_limits', 'business_suite_agent_limits');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('business_suite_accounts') && ! Schema::hasTable('social_media_accounts')) {
            Schema::rename('business_suite_accounts', 'social_media_accounts');
        }

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'business_suite_agent_limits')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->renameColumn('business_suite_agent_limits', 'social_media_agent_limits');
            });
        }
    }
};
