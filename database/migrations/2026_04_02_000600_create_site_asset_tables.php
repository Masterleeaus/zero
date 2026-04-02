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
        // ── Site Assets ───────────────────────────────────────────────────────
        Schema::create('site_assets', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            // Hierarchy linkage
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('building_id')->nullable()->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();

            // Link to Equipment catalogue (optional — may be a bespoke install)
            $table->unsignedBigInteger('equipment_id')->nullable()->index();

            $table->string('label');
            $table->string('asset_code')->nullable()->index();
            $table->string('asset_type', 80)->nullable()
                ->comment('e.g. hvac | pump | fire_panel | alarm | access_control | meter | other');
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location_description')->nullable();

            // Lifecycle
            $table->date('install_date')->nullable();
            $table->date('commission_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->unsignedSmallInteger('inspection_interval_days')->nullable()
                ->comment('Days between required inspections');
            $table->unsignedSmallInteger('maintenance_interval_days')->nullable()
                ->comment('Days between preventive maintenance visits');
            $table->date('next_inspection_due')->nullable();
            $table->date('next_maintenance_due')->nullable();
            $table->date('last_serviced_at')->nullable();

            $table->string('condition_status', 30)->default('good')
                ->comment('new | good | fair | poor | decommissioned');

            $table->string('status', 30)->default('active')
                ->comment('active | removed | replaced | decommissioned');

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status'], 'sa_company_status');
            $table->index(['company_id', 'premises_id'], 'sa_company_premises');
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
        Schema::dropIfExists('site_assets');
    }
};
