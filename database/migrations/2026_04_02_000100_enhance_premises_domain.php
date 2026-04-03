<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage A — Enhance Premises domain
 *
 * Adds structured facility management fields to premises, buildings, and
 * premise_units tables.
 *
 * Sources: FacilityManagement, ManagedPremises, PropertyManagement.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Premises enhancements ─────────────────────────────────────────────
        Schema::table('premises', static function (Blueprint $table) {
            if (! Schema::hasColumn('premises', 'service_priority')) {
                $table->string('service_priority', 20)->nullable()->default('normal')
                    ->comment('low | normal | high | critical')
                    ->after('customer_id');
            }
            if (! Schema::hasColumn('premises', 'maintenance_zone')) {
                $table->string('maintenance_zone')->nullable()->after('service_priority');
            }
            if (! Schema::hasColumn('premises', 'access_level')) {
                $table->string('access_level', 20)->nullable()->default('standard')
                    ->comment('public | standard | restricted | secure')
                    ->after('maintenance_zone');
            }
        });

        // ── Buildings enhancements ────────────────────────────────────────────
        Schema::table('buildings', static function (Blueprint $table) {
            if (! Schema::hasColumn('buildings', 'maintenance_zone')) {
                $table->string('maintenance_zone')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('buildings', 'year_built')) {
                $table->unsignedSmallInteger('year_built')->nullable()->after('maintenance_zone');
            }
            if (! Schema::hasColumn('buildings', 'floors_count')) {
                $table->unsignedSmallInteger('floors_count')->nullable()->after('year_built');
            }
        });

        // ── Units enhancements ────────────────────────────────────────────────
        Schema::table('premise_units', static function (Blueprint $table) {
            if (! Schema::hasColumn('premise_units', 'unit_type')) {
                $table->string('unit_type', 50)->nullable()
                    ->comment('residential | office | retail | storage | common | plant')
                    ->after('notes');
            }
            if (! Schema::hasColumn('premise_units', 'occupancy_status')) {
                $table->string('occupancy_status', 30)->nullable()->default('vacant')
                    ->comment('vacant | occupied | partial | maintenance')
                    ->after('unit_type');
            }
            if (! Schema::hasColumn('premise_units', 'access_level')) {
                $table->string('access_level', 20)->nullable()->default('standard')
                    ->comment('public | standard | restricted | secure')
                    ->after('occupancy_status');
            }
            if (! Schema::hasColumn('premise_units', 'service_priority')) {
                $table->string('service_priority', 20)->nullable()->default('normal')
                    ->after('access_level');
            }
            if (! Schema::hasColumn('premise_units', 'maintenance_zone')) {
                $table->string('maintenance_zone')->nullable()->after('service_priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('premises', static function (Blueprint $table) {
            foreach (['service_priority', 'maintenance_zone', 'access_level'] as $col) {
                if (Schema::hasColumn('premises', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('buildings', static function (Blueprint $table) {
            foreach (['maintenance_zone', 'year_built', 'floors_count'] as $col) {
                if (Schema::hasColumn('buildings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('premise_units', static function (Blueprint $table) {
            foreach (['unit_type', 'occupancy_status', 'access_level', 'service_priority', 'maintenance_zone'] as $col) {
                if (Schema::hasColumn('premise_units', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
