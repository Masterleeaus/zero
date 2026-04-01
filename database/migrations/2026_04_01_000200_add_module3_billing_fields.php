<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 3 — billing integration fields.
 *
 * Adds financial-awareness columns to service_jobs and job_stages so that
 * the Money domain can link, track, and auto-generate invoices from completed
 * field-service work.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Billing fields on service jobs
        Schema::table('service_jobs', function (Blueprint $table) {
            // Whether this job is billable to the customer
            $table->boolean('is_billable')->default(false)->after('resolution');

            // Rate charged per hour (null = use quote/agreement rate)
            $table->decimal('billable_rate', 10, 2)->nullable()->after('is_billable');

            // The invoice generated for this job (nullable until invoiced)
            $table->unsignedBigInteger('invoice_id')->nullable()->after('billable_rate');
            $table->foreign('invoice_id', 'sj_invoice_fk')
                ->references('id')
                ->on('invoices')
                ->nullOnDelete();

            // When the job was invoiced
            $table->dateTime('invoiced_at')->nullable()->after('invoice_id');

            // Indexes for billing queries
            $table->index(['company_id', 'is_billable', 'invoice_id'], 'sj_billing_status');
        });

        // Stage-level invoiceable flag (from fieldservice_account fsm_stage)
        Schema::table('job_stages', function (Blueprint $table) {
            $table->boolean('is_invoiceable')->default(false)->after('is_closed');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign('sj_invoice_fk');
            $table->dropIndex('sj_billing_status');
            $table->dropColumn(['is_billable', 'billable_rate', 'invoice_id', 'invoiced_at']);
        });

        Schema::table('job_stages', function (Blueprint $table) {
            $table->dropColumn('is_invoiceable');
        });
    }
};
