<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ext_chatbots')) {
            return;
        }

        Schema::table('ext_chatbots', function (Blueprint $table) {
            if (! Schema::hasColumn('ext_chatbots', 'is_customer_portal')) {
                $table->boolean('is_customer_portal')->default(false)->after('is_booking_assistant');
            }

            if (! Schema::hasColumn('ext_chatbots', 'portal_home_title')) {
                $table->string('portal_home_title')->nullable()->after('is_customer_portal');
            }

            if (! Schema::hasColumn('ext_chatbots', 'portal_primary_cta')) {
                $table->string('portal_primary_cta')->nullable()->after('portal_home_title');
            }

            if (! Schema::hasColumn('ext_chatbots', 'portal_modules')) {
                $table->json('portal_modules')->nullable()->after('portal_primary_cta');
            }

            if (! Schema::hasColumn('ext_chatbots', 'portal_quick_actions')) {
                $table->json('portal_quick_actions')->nullable()->after('portal_modules');
            }

            if (! Schema::hasColumn('ext_chatbots', 'portal_settings')) {
                $table->json('portal_settings')->nullable()->after('portal_quick_actions');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ext_chatbots')) {
            return;
        }

        Schema::table('ext_chatbots', function (Blueprint $table) {
            foreach ([
                'is_customer_portal',
                'portal_home_title',
                'portal_primary_cta',
                'portal_modules',
                'portal_quick_actions',
                'portal_settings',
            ] as $column) {
                if (Schema::hasColumn('ext_chatbots', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
