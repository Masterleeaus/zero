<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'service_jobs_company_status_index');
            $table->index(['company_id', 'assigned_user_id'], 'service_jobs_company_assigned_user_index');
            $table->index('agreement_id', 'service_jobs_agreement_id_index');
            $table->index('scheduled_at', 'service_jobs_scheduled_at_index');
        });

        Schema::table('quotes', static function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'quotes_company_status_index');
            $table->index(['company_id', 'customer_id'], 'quotes_company_customer_index');
        });

        Schema::table('invoices', static function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'invoices_company_status_index');
            $table->index(['company_id', 'due_date'], 'invoices_company_due_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->dropIndex('service_jobs_company_status_index');
            $table->dropIndex('service_jobs_company_assigned_user_index');
            $table->dropIndex('service_jobs_agreement_id_index');
            $table->dropIndex('service_jobs_scheduled_at_index');
        });

        Schema::table('quotes', static function (Blueprint $table) {
            $table->dropIndex('quotes_company_status_index');
            $table->dropIndex('quotes_company_customer_index');
        });

        Schema::table('invoices', static function (Blueprint $table) {
            $table->dropIndex('invoices_company_status_index');
            $table->dropIndex('invoices_company_due_date_index');
        });
    }
};
