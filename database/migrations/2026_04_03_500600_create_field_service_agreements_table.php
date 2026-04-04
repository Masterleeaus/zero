<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Modules — fieldservice_sale + fieldservice_sale_agreement + fieldservice_sale_recurring
 *
 * Creates the field_service_agreements table and extends service_jobs and
 * service_plan_visits with agreement linkage columns.
 *
 * STAGE A — field_service_agreements table
 *   Maps: Quote → FieldServiceAgreement → Visits → Jobs → Invoices
 *
 * STAGE B — service_jobs extensions
 *   contract_visit_id   : the ServicePlanVisit that spawned this job
 *   recurring_source_id : the FieldServiceAgreement that is the recurring source
 *
 * STAGE C — service_plan_visits extensions
 *   field_service_agreement_id : the FieldServiceAgreement driving this visit
 *   sale_line_id                : the QuoteItem (sale order line) that originated this visit
 */
return new class extends Migration {
    public function up(): void
    {
        // ── field_service_agreements ─────────────────────────────────────────
        if (! Schema::hasTable('field_service_agreements')) {
            Schema::create('field_service_agreements', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('premises_id')->nullable()->index();
                $table->unsignedBigInteger('quote_id')->nullable()->index();
                $table->string('title')->nullable();
                $table->string('reference')->nullable()->index();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('billing_cycle', 50)->default('monthly')
                    ->comment('monthly | quarterly | annually | one_off');
                $table->string('service_frequency', 50)->default('monthly')
                    ->comment('weekly | fortnightly | monthly | quarterly | custom');
                $table->string('status', 50)->default('draft')
                    ->comment('draft | active | suspended | expired | cancelled | renewed');
                $table->json('terms_json')->nullable()
                    ->comment('Agreement terms and conditions as structured JSON');
                $table->boolean('auto_generate_jobs')->default(false);
                $table->boolean('auto_generate_visits')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'status'], 'fsa_company_status');
                $table->index(['customer_id', 'status'], 'fsa_customer_status');
                $table->index(['company_id', 'end_date'], 'fsa_company_end_date');
            });
        }

        // ── service_jobs: contract visit + recurring source linkage ───────────
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'contract_visit_id')) {
                $table->unsignedBigInteger('contract_visit_id')
                    ->nullable()
                    ->after('sale_line_id')
                    ->comment('The ServicePlanVisit that spawned this job (contract visit origin)');
                $table->index('contract_visit_id', 'sj_contract_visit_id');
            }
            if (! Schema::hasColumn('service_jobs', 'recurring_source_id')) {
                $table->unsignedBigInteger('recurring_source_id')
                    ->nullable()
                    ->after('contract_visit_id')
                    ->comment('The FieldServiceAgreement recurring commercial source for this job');
                $table->index('recurring_source_id', 'sj_recurring_source_id');
            }
        });

        // ── service_plan_visits: FSA + sale line linkage ──────────────────────
        Schema::table('service_plan_visits', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_plan_visits', 'field_service_agreement_id')) {
                $table->unsignedBigInteger('field_service_agreement_id')
                    ->nullable()
                    ->after('sale_agreement_id')
                    ->comment('The FieldServiceAgreement that drives this visit\'s schedule');
                $table->index('field_service_agreement_id', 'spv_fsa_id');
            }
            if (! Schema::hasColumn('service_plan_visits', 'sale_line_id')) {
                $table->unsignedBigInteger('sale_line_id')
                    ->nullable()
                    ->after('field_service_agreement_id')
                    ->comment('The QuoteItem (sale order line) that originated this visit');
                $table->index('sale_line_id', 'spv_sale_line_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_visits', static function (Blueprint $table) {
            $table->dropIndex('spv_sale_line_id');
            $table->dropColumn('sale_line_id');
            $table->dropIndex('spv_fsa_id');
            $table->dropColumn('field_service_agreement_id');
        });

        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->dropIndex('sj_recurring_source_id');
            $table->dropColumn('recurring_source_id');
            $table->dropIndex('sj_contract_visit_id');
            $table->dropColumn('contract_visit_id');
        });

        Schema::dropIfExists('field_service_agreements');
    }
};
