<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Modules — fieldservice_sale + fieldservice_sale_agreement
 *
 * Adds the sale-linkage columns required by the field-service sale pipeline:
 *
 * service_jobs
 *   - sale_line_id  : which quote_item line generated this job (Odoo: sale_line_id on fsm.order)
 *
 * quote_items
 *   - field_service_tracking : none | sale | line  (Odoo: product.field_service_tracking)
 *   - service_tracking_type  : freeform label for UI grouping
 *
 * quotes
 *   - premises_id : physical delivery site for this quote (Odoo: fsm_location_id on sale.order)
 *
 * service_agreements
 *   - originating_quote_id : the Quote that activated/created this agreement
 */
return new class extends Migration {
    public function up(): void
    {
        // ── service_jobs: link to originating quote line ──────────────────────
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'sale_line_id')) {
                $table->unsignedBigInteger('sale_line_id')
                    ->nullable()
                    ->after('quote_id')
                    ->comment('Quote item (line) that generated this field-service job');
                $table->index('sale_line_id');
                $table->foreign('sale_line_id')
                    ->references('id')->on('quote_items')
                    ->onDelete('set null');
            }
        });

        // ── quote_items: field-service tracking behaviour per line ────────────
        Schema::table('quote_items', static function (Blueprint $table) {
            if (! Schema::hasColumn('quote_items', 'field_service_tracking')) {
                $table->string('field_service_tracking')
                    ->default('no')
                    ->after('sort_order')
                    ->comment('none|sale|line — controls job-generation on quote acceptance');
            }
            if (! Schema::hasColumn('quote_items', 'service_tracking_type')) {
                $table->string('service_tracking_type')
                    ->nullable()
                    ->after('field_service_tracking')
                    ->comment('Freeform label (e.g. install, maintenance, inspection)');
            }
        });

        // ── quotes: physical service location ────────────────────────────────
        Schema::table('quotes', static function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'premises_id')) {
                $table->unsignedBigInteger('premises_id')
                    ->nullable()
                    ->after('site_id')
                    ->comment('Premises / FSM location for service delivery');
                $table->index('premises_id');
            }
        });

        // ── service_agreements: trace back to originating quote ───────────────
        Schema::table('service_agreements', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_agreements', 'originating_quote_id')) {
                $table->unsignedBigInteger('originating_quote_id')
                    ->nullable()
                    ->after('quote_id')
                    ->comment('Quote that activated or created this agreement via a sale');
                $table->index('originating_quote_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'sale_line_id')) {
                $table->dropForeign(['sale_line_id']);
                $table->dropIndex(['sale_line_id']);
                $table->dropColumn('sale_line_id');
            }
        });

        Schema::table('quote_items', static function (Blueprint $table) {
            foreach (['field_service_tracking', 'service_tracking_type'] as $col) {
                if (Schema::hasColumn('quote_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('quotes', static function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'premises_id')) {
                $table->dropIndex(['premises_id']);
                $table->dropColumn('premises_id');
            }
        });

        Schema::table('service_agreements', static function (Blueprint $table) {
            if (Schema::hasColumn('service_agreements', 'originating_quote_id')) {
                $table->dropIndex(['originating_quote_id']);
                $table->dropColumn('originating_quote_id');
            }
        });
    }
};
