<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage C — Site Asset Domain
 *
 * Tracks site-installed assets separate from stock inventory.
 * An asset here represents a physical installed item (fixed infrastructure,
 * tracked service components) at a Premises / Unit level.
 *
 *   site_assets          — installed physical asset registry
 *   asset_service_events — inspection / maintenance / repair / replacement log
 *
 * Sources: FacilityManagement/Entities/Asset.php,
 *          AssetManagement entities and migrations,
 *          ManagedPremises/Entities/PropertyAsset.php.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Site Assets (extend existing table from Stage I) ──────────────────
        Schema::table('site_assets', static function (Blueprint $table) {
            if (! Schema::hasColumn('site_assets', 'equipment_id')) {
                $table->unsignedBigInteger('equipment_id')->nullable()->index()->after('unit_id');
            }
            if (! Schema::hasColumn('site_assets', 'label')) {
                $table->string('label')->nullable()->after('equipment_id');
            }
            if (! Schema::hasColumn('site_assets', 'asset_type')) {
                $table->string('asset_type', 80)->nullable()
                    ->comment('hvac | pump | fire_panel | alarm | access_control | meter | other')
                    ->after('label');
            }
            if (! Schema::hasColumn('site_assets', 'model_number')) {
                $table->string('model_number')->nullable()->after('manufacturer');
            }
            if (! Schema::hasColumn('site_assets', 'commission_date')) {
                $table->date('commission_date')->nullable()->after('install_date');
            }
            if (! Schema::hasColumn('site_assets', 'inspection_interval_days')) {
                $table->unsignedSmallInteger('inspection_interval_days')->nullable()
                    ->comment('Days between required inspections')->after('warranty_expiry');
            }
            if (! Schema::hasColumn('site_assets', 'maintenance_interval_days')) {
                $table->unsignedSmallInteger('maintenance_interval_days')->nullable()
                    ->comment('Days between preventive maintenance visits')->after('inspection_interval_days');
            }
            if (! Schema::hasColumn('site_assets', 'next_inspection_due')) {
                $table->date('next_inspection_due')->nullable()->after('maintenance_interval_days');
            }
            if (! Schema::hasColumn('site_assets', 'next_maintenance_due')) {
                $table->date('next_maintenance_due')->nullable()->after('next_inspection_due');
            }
            if (! Schema::hasColumn('site_assets', 'last_serviced_at')) {
                $table->date('last_serviced_at')->nullable()->after('next_maintenance_due');
            }
            if (! Schema::hasColumn('site_assets', 'condition_status')) {
                $table->string('condition_status', 30)->default('good')
                    ->comment('new | good | fair | poor | decommissioned')->after('last_serviced_at');
            }
            if (! Schema::hasColumn('site_assets', 'meta')) {
                $table->json('meta')->nullable()->after('notes');
            }
        });

        // ── Asset Service Events ──────────────────────────────────────────────
        Schema::create('asset_service_events', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('site_asset_id')->index();

            // Optional service job that triggered / executed this event
            $table->unsignedBigInteger('service_job_id')->nullable()->index();

            $table->string('event_type', 40)->default('maintenance')
                ->comment('inspection | maintenance | repair | replacement | installation | decommission');

            $table->date('event_date')->nullable();
            $table->string('status', 30)->default('completed')
                ->comment('scheduled | in_progress | completed | failed');

            $table->text('description')->nullable();
            $table->text('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->decimal('cost', 12, 2)->nullable();

            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('site_asset_id')
                ->references('id')->on('site_assets')->onDelete('cascade');
            $table->index(['company_id', 'event_type'], 'ase_company_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_service_events');

        // Remove extended columns only — do not drop site_assets itself
        Schema::table('site_assets', static function (Blueprint $table) {
            $cols = [
                'equipment_id', 'label', 'asset_type', 'model_number',
                'commission_date', 'inspection_interval_days', 'maintenance_interval_days',
                'next_inspection_due', 'next_maintenance_due', 'last_serviced_at',
                'condition_status', 'meta',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('site_assets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
