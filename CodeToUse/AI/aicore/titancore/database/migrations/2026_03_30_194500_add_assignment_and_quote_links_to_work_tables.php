<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'quote_id')) {
                $table->unsignedBigInteger('quote_id')->nullable()->after('site_id')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'assigned_user_id')) {
                $table->unsignedBigInteger('assigned_user_id')->nullable()->after('customer_id')->index();
            }
        });

        Schema::table('checklists', static function (Blueprint $table) {
            if (! Schema::hasColumn('checklists', 'assigned_user_id')) {
                $table->unsignedBigInteger('assigned_user_id')->nullable()->after('service_job_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'quote_id')) {
                $table->dropColumn('quote_id');
            }
            if (Schema::hasColumn('service_jobs', 'assigned_user_id')) {
                $table->dropColumn('assigned_user_id');
            }
        });

        Schema::table('checklists', static function (Blueprint $table) {
            if (Schema::hasColumn('checklists', 'assigned_user_id')) {
                $table->dropColumn('assigned_user_id');
            }
        });
    }
};
