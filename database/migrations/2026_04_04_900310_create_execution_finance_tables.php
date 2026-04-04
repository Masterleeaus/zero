<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 09 — ExecutionFinanceLayer
 *
 * Creates:
 *   - job_cost_records
 *   - job_revenue_records
 *   - job_financial_summaries
 *   - financial_rollups
 *
 * Extends service_jobs with finance columns (all guarded with hasColumn checks).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Job Cost Records ───────────────────────────────────────────────
        Schema::create('job_cost_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('job_id')->index();
            $table->enum('cost_type', ['labour', 'materials', 'travel', 'subcontract', 'overhead', 'other']);
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 3)->default(1.000);
            $table->decimal('unit_cost', 12, 4)->default(0.0000);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->unsignedBigInteger('recorded_by')->nullable()->index();
            $table->date('cost_date')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('service_jobs')->cascadeOnDelete();
        });

        // ── 2. Job Revenue Records ────────────────────────────────────────────
        Schema::create('job_revenue_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('job_id')->index();
            $table->enum('revenue_type', ['labour', 'materials', 'call_out', 'surcharge', 'contract_allocation', 'other']);
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 3)->default(1.000);
            $table->decimal('unit_price', 12, 4)->default(0.0000);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->enum('billing_source', ['agreement', 'quote', 'ad_hoc', 'time_and_materials']);
            $table->unsignedBigInteger('agreement_id')->nullable()->index();
            $table->boolean('is_invoiced')->default(false);
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('service_jobs')->cascadeOnDelete();
        });

        // ── 3. Job Financial Summaries ────────────────────────────────────────
        Schema::create('job_financial_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->unique()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->decimal('gross_margin', 12, 2)->default(0.00);
            $table->decimal('gross_margin_pct', 6, 4)->default(0.0000);
            $table->decimal('labour_cost', 12, 2)->default(0.00);
            $table->decimal('materials_cost', 12, 2)->default(0.00);
            $table->decimal('travel_cost', 12, 2)->default(0.00);
            $table->boolean('is_profitable')->default(false);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('service_jobs')->cascadeOnDelete();
        });

        // ── 4. Financial Rollups ──────────────────────────────────────────────
        Schema::create('financial_rollups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->enum('rollup_type', ['customer', 'premises', 'agreement', 'technician', 'job_type', 'territory', 'month']);
            $table->string('rollup_key');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->unsignedInteger('job_count')->default(0);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->decimal('gross_margin', 12, 2)->default(0.00);
            $table->decimal('gross_margin_pct', 6, 4)->default(0.0000);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'rollup_type', 'rollup_key']);
        });

        // ── 5. Extend service_jobs with finance columns ───────────────────────
        Schema::table('service_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'quoted_amount')) {
                $table->decimal('quoted_amount', 12, 2)->nullable()->after('billable_rate');
            }
            if (! Schema::hasColumn('service_jobs', 'actual_cost')) {
                $table->decimal('actual_cost', 12, 2)->nullable()->after('quoted_amount');
            }
            if (! Schema::hasColumn('service_jobs', 'actual_revenue')) {
                $table->decimal('actual_revenue', 12, 2)->nullable()->after('actual_cost');
            }
            if (! Schema::hasColumn('service_jobs', 'margin_pct')) {
                $table->decimal('margin_pct', 6, 4)->nullable()->after('actual_revenue');
            }
            if (! Schema::hasColumn('service_jobs', 'billing_status')) {
                $table->string('billing_status', 20)->nullable()->after('margin_pct');
            }
            // invoiced_at already exists — guard just in case
            if (! Schema::hasColumn('service_jobs', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('billing_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $columns = ['quoted_amount', 'actual_cost', 'actual_revenue', 'margin_pct', 'billing_status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('service_jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('financial_rollups');
        Schema::dropIfExists('job_financial_summaries');
        Schema::dropIfExists('job_revenue_records');
        Schema::dropIfExists('job_cost_records');
    }
};
