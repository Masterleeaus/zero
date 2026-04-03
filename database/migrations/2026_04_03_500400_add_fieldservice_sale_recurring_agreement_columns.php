<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: fieldservice_sale_recurring_agreement
 *
 * Extends the canonical Work domain tables with columns required to track
 * the commercial origin of recurring service agreements, plans, and visits.
 *
 * Mirrors Odoo module: fieldservice_sale_recurring_agreement
 *
 * Extends:
 *   service_agreements  — recurring source, commercial terms, commitment counts
 *   service_plans       — sale-origin flag, recurring type, commercial date range
 *   service_plan_visits — sale-origin flag, agreement link
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── service_agreements ────────────────────────────────────────────────
        Schema::table('service_agreements', function (Blueprint $table) {
            // How this recurring agreement was created
            $table->string('recurring_source')->nullable()->after('status')
                ->comment('sale | manual — how recurring was set up');

            // Commercial recurrence terms encoded from the originating quote/sale
            $table->json('sale_recurrence_terms')->nullable()->after('recurring_source')
                ->comment('JSON: frequency, interval, visits_per_cycle, start, end from the originating sale');

            // The renewal/extension quote that last updated this agreement
            $table->unsignedBigInteger('renewal_quote_id')->nullable()->after('originating_quote_id');
            $table->foreign('renewal_quote_id')->references('id')->on('quotes')->nullOnDelete();

            // Commercial coverage window from the originating sale
            $table->date('commercial_start_date')->nullable()->after('renewal_quote_id');
            $table->date('commercial_end_date')->nullable()->after('commercial_start_date');

            // Number of visits committed under the sale and how many have been consumed
            $table->unsignedInteger('committed_visits')->nullable()->after('commercial_end_date');
            $table->unsignedInteger('covered_visits_used')->default(0)->after('committed_visits');
        });

        // ── service_plans ─────────────────────────────────────────────────────
        Schema::table('service_plans', function (Blueprint $table) {
            // Whether this plan was generated from a sale/recurring agreement
            $table->boolean('originated_from_sale')->default(false)->after('agreement_id');

            // The type of recurring sale commitment: committed | usage_based | time_and_material
            $table->string('sale_recurring_type')->nullable()->after('originated_from_sale');

            // Number of visits committed at point of sale
            $table->unsignedInteger('commercial_visits_committed')->nullable()->after('sale_recurring_type');

            // Commercial coverage window (may differ from plan starts_on / ends_on)
            $table->date('commercial_start_date')->nullable()->after('commercial_visits_committed');
            $table->date('commercial_end_date')->nullable()->after('commercial_start_date');

            // Direct FK to the service_agreement that was sold (for quick lookup)
            $table->unsignedBigInteger('sale_agreement_id')->nullable()->after('commercial_end_date');
            $table->foreign('sale_agreement_id')->references('id')->on('service_agreements')->nullOnDelete();
        });

        // ── service_plan_visits ───────────────────────────────────────────────
        Schema::table('service_plan_visits', function (Blueprint $table) {
            // Whether this visit was projected from a sale-originated plan
            $table->boolean('sale_originated')->default(false)->after('status');

            // Direct FK to the service_agreement that funded this visit
            $table->unsignedBigInteger('sale_agreement_id')->nullable()->after('sale_originated');
            $table->foreign('sale_agreement_id')->references('id')->on('service_agreements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_visits', function (Blueprint $table) {
            $table->dropForeign(['sale_agreement_id']);
            $table->dropColumn(['sale_originated', 'sale_agreement_id']);
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropForeign(['sale_agreement_id']);
            $table->dropColumn([
                'originated_from_sale',
                'sale_recurring_type',
                'commercial_visits_committed',
                'commercial_start_date',
                'commercial_end_date',
                'sale_agreement_id',
            ]);
        });

        Schema::table('service_agreements', function (Blueprint $table) {
            $table->dropForeign(['renewal_quote_id']);
            $table->dropColumn([
                'recurring_source',
                'sale_recurrence_terms',
                'renewal_quote_id',
                'commercial_start_date',
                'commercial_end_date',
                'committed_visits',
                'covered_visits_used',
            ]);
        });
    }
};
