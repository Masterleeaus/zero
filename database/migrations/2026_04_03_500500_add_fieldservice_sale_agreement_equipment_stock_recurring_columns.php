<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Modules — fieldservice_sale_agreement_equipment_stock + fieldservice_sale_recurring
 *
 * Extends the canonical lifecycle graph to support:
 *
 * STAGE B — Agreement-sold equipment coverage
 *   installed_equipment
 *     - agreement_id           : service agreement that covers this installation
 *     - sale_quote_id          : quote through which this equipment was sold
 *     - coverage_start_date    : date coverage activated for this unit
 *     - coverage_end_date      : date coverage expires for this unit
 *     - coverage_activated_at  : timestamp when coverage was first activated
 *
 * STAGE C — Recurring sale engine columns
 *   service_plans
 *     - origin_quote_id        : quote that triggered recurring plan generation
 *     - recurring_product_ref  : product/sku reference for the recurring service
 *     - recurrence_type        : maintenance | inspection | compliance | contract
 *     - auto_generate_visits   : whether visits are auto-created on schedule advance
 *     - equipment_scope        : json array of installed_equipment IDs in scope
 *
 *   service_agreements
 *     - has_equipment_coverage : flag — this agreement covers sold equipment
 *     - recurring_plan_count   : cached count of active recurring plans
 *
 *   service_plan_visits
 *     - installed_equipment_id : specific installed equipment this visit services
 *     - coverage_source        : agreement | warranty | manual
 *     - recurring_sale_ref     : trace back to originating recurring sale line
 */
return new class extends Migration {
    public function up(): void
    {
        // ── installed_equipment: agreement + sale coverage linkage ─────────────
        Schema::table('installed_equipment', static function (Blueprint $table) {
            if (! Schema::hasColumn('installed_equipment', 'agreement_id')) {
                $table->unsignedBigInteger('agreement_id')
                    ->nullable()
                    ->after('service_job_id')
                    ->comment('Service agreement that covers this installation');
                $table->index('agreement_id', 'ie_agreement_id');
            }
            if (! Schema::hasColumn('installed_equipment', 'sale_quote_id')) {
                $table->unsignedBigInteger('sale_quote_id')
                    ->nullable()
                    ->after('agreement_id')
                    ->comment('Quote through which this equipment was sold / activated');
                $table->index('sale_quote_id', 'ie_sale_quote_id');
            }
            if (! Schema::hasColumn('installed_equipment', 'coverage_start_date')) {
                $table->date('coverage_start_date')
                    ->nullable()
                    ->after('sale_quote_id')
                    ->comment('Date coverage activated for this equipment instance');
            }
            if (! Schema::hasColumn('installed_equipment', 'coverage_end_date')) {
                $table->date('coverage_end_date')
                    ->nullable()
                    ->after('coverage_start_date')
                    ->comment('Date coverage expires for this equipment instance');
            }
            if (! Schema::hasColumn('installed_equipment', 'coverage_activated_at')) {
                $table->timestamp('coverage_activated_at')
                    ->nullable()
                    ->after('coverage_end_date')
                    ->comment('Timestamp when agreement coverage was first activated');
            }
        });

        // ── service_plans: recurring origin + scope ────────────────────────────
        Schema::table('service_plans', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_plans', 'origin_quote_id')) {
                $table->unsignedBigInteger('origin_quote_id')
                    ->nullable()
                    ->after('agreement_id')
                    ->comment('Quote that triggered recurring plan generation');
                $table->index('origin_quote_id', 'sp_origin_quote_id');
            }
            if (! Schema::hasColumn('service_plans', 'recurring_product_ref')) {
                $table->string('recurring_product_ref', 120)
                    ->nullable()
                    ->after('origin_quote_id')
                    ->comment('Product / SKU reference for the recurring service sold');
            }
            if (! Schema::hasColumn('service_plans', 'recurrence_type')) {
                $table->string('recurrence_type', 60)
                    ->nullable()
                    ->after('recurring_product_ref')
                    ->comment('maintenance | inspection | compliance | contract');
            }
            if (! Schema::hasColumn('service_plans', 'auto_generate_visits')) {
                $table->boolean('auto_generate_visits')
                    ->default(true)
                    ->after('recurrence_type')
                    ->comment('Auto-create visits when plan advances to next cycle');
            }
            if (! Schema::hasColumn('service_plans', 'equipment_scope')) {
                $table->json('equipment_scope')
                    ->nullable()
                    ->after('auto_generate_visits')
                    ->comment('JSON array of installed_equipment IDs covered by this plan');
            }
        });

        // ── service_agreements: equipment coverage flags ───────────────────────
        Schema::table('service_agreements', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_agreements', 'has_equipment_coverage')) {
                $table->boolean('has_equipment_coverage')
                    ->default(false)
                    ->after('originating_quote_id')
                    ->comment('Flag: this agreement covers agreement-sold equipment');
            }
            if (! Schema::hasColumn('service_agreements', 'recurring_plan_count')) {
                $table->unsignedSmallInteger('recurring_plan_count')
                    ->default(0)
                    ->after('has_equipment_coverage')
                    ->comment('Cached count of active recurring service plans');
            }
        });

        // ── service_plan_visits: coverage context ─────────────────────────────
        Schema::table('service_plan_visits', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_plan_visits', 'installed_equipment_id')) {
                $table->unsignedBigInteger('installed_equipment_id')
                    ->nullable()
                    ->after('service_job_id')
                    ->comment('Specific installed equipment this visit services');
                $table->index('installed_equipment_id', 'spv_installed_equipment_id');
            }
            if (! Schema::hasColumn('service_plan_visits', 'coverage_source')) {
                $table->string('coverage_source', 40)
                    ->nullable()
                    ->after('installed_equipment_id')
                    ->comment('agreement | warranty | manual — origin of coverage for this visit');
            }
            if (! Schema::hasColumn('service_plan_visits', 'recurring_sale_ref')) {
                $table->string('recurring_sale_ref', 120)
                    ->nullable()
                    ->after('coverage_source')
                    ->comment('Trace reference back to the originating recurring sale line');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installed_equipment', static function (Blueprint $table) {
            foreach (['agreement_id', 'sale_quote_id', 'coverage_start_date', 'coverage_end_date', 'coverage_activated_at'] as $col) {
                if (Schema::hasColumn('installed_equipment', $col)) {
                    if (in_array($col, ['agreement_id', 'sale_quote_id'])) {
                        try {
                            $table->dropIndex('ie_' . $col);
                        } catch (\Exception $e) {
                        }
                    }
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_plans', static function (Blueprint $table) {
            foreach (['origin_quote_id', 'recurring_product_ref', 'recurrence_type', 'auto_generate_visits', 'equipment_scope'] as $col) {
                if (Schema::hasColumn('service_plans', $col)) {
                    if ($col === 'origin_quote_id') {
                        try {
                            $table->dropIndex('sp_origin_quote_id');
                        } catch (\Exception $e) {
                        }
                    }
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_agreements', static function (Blueprint $table) {
            foreach (['has_equipment_coverage', 'recurring_plan_count'] as $col) {
                if (Schema::hasColumn('service_agreements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_plan_visits', static function (Blueprint $table) {
            foreach (['installed_equipment_id', 'coverage_source', 'recurring_sale_ref'] as $col) {
                if (Schema::hasColumn('service_plan_visits', $col)) {
                    if ($col === 'installed_equipment_id') {
                        try {
                            $table->dropIndex('spv_installed_equipment_id');
                        } catch (\Exception $e) {
                        }
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
