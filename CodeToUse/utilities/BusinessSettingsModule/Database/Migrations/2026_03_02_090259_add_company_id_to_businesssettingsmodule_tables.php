<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('business_page_settings') && !Schema::hasColumn('business_page_settings', 'company_id')) {
            Schema::table('business_page_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('business_settings') && !Schema::hasColumn('business_settings', 'company_id')) {
            Schema::table('business_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('cron_jobs') && !Schema::hasColumn('cron_jobs', 'company_id')) {
            Schema::table('cron_jobs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('data_settings') && !Schema::hasColumn('data_settings', 'company_id')) {
            Schema::table('data_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('error_logs') && !Schema::hasColumn('error_logs', 'company_id')) {
            Schema::table('error_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('landing_page_features') && !Schema::hasColumn('landing_page_features', 'company_id')) {
            Schema::table('landing_page_features', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('landing_page_specialities') && !Schema::hasColumn('landing_page_specialities', 'company_id')) {
            Schema::table('landing_page_specialities', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('landing_page_testimonials') && !Schema::hasColumn('landing_page_testimonials', 'company_id')) {
            Schema::table('landing_page_testimonials', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('login_setups') && !Schema::hasColumn('login_setups', 'company_id')) {
            Schema::table('login_setups', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('notification_setups') && !Schema::hasColumn('notification_setups', 'company_id')) {
            Schema::table('notification_setups', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('package_subscriber_features') && !Schema::hasColumn('package_subscriber_features', 'company_id')) {
            Schema::table('package_subscriber_features', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('package_subscriber_limits') && !Schema::hasColumn('package_subscriber_limits', 'company_id')) {
            Schema::table('package_subscriber_limits', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('package_subscriber_logs') && !Schema::hasColumn('package_subscriber_logs', 'company_id')) {
            Schema::table('package_subscriber_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('package_subscribers') && !Schema::hasColumn('package_subscribers', 'company_id')) {
            Schema::table('package_subscribers', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('provider_notification_setups') && !Schema::hasColumn('provider_notification_setups', 'company_id')) {
            Schema::table('provider_notification_setups', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('seo_settings') && !Schema::hasColumn('seo_settings', 'company_id')) {
            Schema::table('seo_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('settings_tutorials') && !Schema::hasColumn('settings_tutorials', 'company_id')) {
            Schema::table('settings_tutorials', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('storages') && !Schema::hasColumn('storages', 'company_id')) {
            Schema::table('storages', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('subscription_package_features') && !Schema::hasColumn('subscription_package_features', 'company_id')) {
            Schema::table('subscription_package_features', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('subscription_package_limits') && !Schema::hasColumn('subscription_package_limits', 'company_id')) {
            Schema::table('subscription_package_limits', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('subscription_packages') && !Schema::hasColumn('subscription_packages', 'company_id')) {
            Schema::table('subscription_packages', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('translations') && !Schema::hasColumn('translations', 'company_id')) {
            Schema::table('translations', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};
