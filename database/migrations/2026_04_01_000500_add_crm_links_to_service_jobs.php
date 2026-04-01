<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 6 — fieldservice_crm
 *
 * Links service_jobs to the host CRM: enquiries (leads) and deals (opportunities).
 * Mirrors the Odoo fieldservice_crm `opportunity_id` FK on fsm.order, mapped to
 * the Titan Zero enquiry/deal vocabulary.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'enquiry_id')) {
                $table->unsignedBigInteger('enquiry_id')
                    ->nullable()
                    ->after('customer_id')
                    ->comment('CRM lead / enquiry that originated this job');
                $table->index('enquiry_id');
                $table->foreign('enquiry_id')->references('id')->on('enquiries')->onDelete('set null');
            }

            if (! Schema::hasColumn('service_jobs', 'deal_id')) {
                $table->unsignedBigInteger('deal_id')
                    ->nullable()
                    ->after('enquiry_id')
                    ->comment('CRM opportunity / deal that originated this job');
                $table->index('deal_id');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'enquiry_id')) {
                $table->dropForeign(['enquiry_id']);
                $table->dropIndex(['enquiry_id']);
                $table->dropColumn('enquiry_id');
            }

            if (Schema::hasColumn('service_jobs', 'deal_id')) {
                $table->dropForeign(['deal_id']);
                $table->dropIndex(['deal_id']);
                $table->dropColumn('deal_id');
            }
        });
    }
};
