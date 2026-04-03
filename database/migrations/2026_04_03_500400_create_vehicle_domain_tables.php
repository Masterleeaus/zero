<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Modules 24+25 — fieldservice_vehicle + fieldservice_vehicle_stock
 *
 * Creates:
 *   vehicles                   — canonical crew vehicle records
 *   vehicle_assignments        — polymorphic vehicle-to-entity links
 *   vehicle_stock              — stock lines onboard a vehicle
 *   vehicle_equipment          — equipment items carried by a vehicle
 *   vehicle_location_snapshots — lightweight location point-in-time records
 *
 * Extends:
 *   shifts           — vehicle_id column
 *   dispatch_routes  — vehicle_id column
 *   service_jobs     — assigned_vehicle_id + required_vehicle_type columns
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── vehicles ─────────────────────────────────────────────────────────
        Schema::create('vehicles', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();

            $table->string('name');
            $table->string('registration')->nullable();
            $table->string('vehicle_type', 30)->default('van')
                ->comment('van|truck|car|motorcycle|trailer|other');

            // Crew / team link
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_driver_id')->nullable()->index();

            // Physical attributes
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('capacity_kg')->nullable()
                ->comment('Payload capacity in kilograms');

            // Capability tags stored as JSON array of strings
            $table->json('capability_tags')->nullable()
                ->comment('E.g. ["ladder","chemical_safe","equipment_transport"]');

            $table->string('status', 30)->default('active')
                ->comment('active|in_use|servicing|retired');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'v_company_status');
        });

        // ── vehicle_assignments ───────────────────────────────────────────────
        Schema::create('vehicle_assignments', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();

            // Polymorphic link (service_job | dispatch_route | shift)
            $table->string('assignable_type', 60);
            $table->unsignedBigInteger('assignable_id');

            $table->unsignedBigInteger('assigned_by')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id'], 'va_assignable');
            $table->index(['vehicle_id', 'ended_at'], 'va_vehicle_active');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });

        // ── vehicle_stock ─────────────────────────────────────────────────────
        Schema::create('vehicle_stock', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();

            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('quantity_reserved', 10, 3)->default(0);
            $table->decimal('quantity_consumed', 10, 3)->default(0);
            $table->string('unit', 30)->nullable()
                ->comment('E.g. ea, kg, L');

            // Optional job reservation link
            $table->unsignedBigInteger('reserved_for_job_id')->nullable()->index();

            $table->string('status', 30)->default('available')
                ->comment('available|reserved|consumed|returned');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'status'], 'vs_vehicle_status');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');

            $table->foreign('reserved_for_job_id')
                ->references('id')
                ->on('service_jobs')
                ->onDelete('set null');
        });

        // ── vehicle_equipment ─────────────────────────────────────────────────
        Schema::create('vehicle_equipment', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();

            // Optional FK to canonical equipment table
            $table->unsignedBigInteger('equipment_id')->nullable()->index();

            // Freeform label used when no canonical record exists
            $table->string('equipment_label')->nullable();

            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('condition', 30)->default('good')
                ->comment('good|fair|poor|out_of_service');

            $table->timestamp('loaded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });

        // ── vehicle_location_snapshots ────────────────────────────────────────
        Schema::create('vehicle_location_snapshots', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamp('captured_at')->useCurrent();
            $table->string('source', 20)->default('mobile')
                ->comment('mobile|gps|manual|system');
            $table->float('accuracy')->nullable()
                ->comment('Accuracy radius in metres');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'captured_at'], 'vls_vehicle_time');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });

        // ── Extend shifts: vehicle_id ─────────────────────────────────────────
        Schema::table('shifts', static function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('user_id')
                ->index();
        });

        // ── Extend dispatch_routes: vehicle_id ───────────────────────────────
        Schema::table('dispatch_routes', static function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('team_id')
                ->index();
        });

        // ── Extend service_jobs: vehicle columns ──────────────────────────────
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_vehicle_id')->nullable()
                ->after('assigned_user_id')->index();
            $table->string('required_vehicle_type', 30)->nullable()
                ->after('assigned_vehicle_id')
                ->comment('Capability constraint, e.g. ladder|chemical_safe');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->dropColumn(['assigned_vehicle_id', 'required_vehicle_type']);
        });

        Schema::table('dispatch_routes', static function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });

        Schema::table('shifts', static function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });

        Schema::dropIfExists('vehicle_location_snapshots');
        Schema::dropIfExists('vehicle_equipment');
        Schema::dropIfExists('vehicle_stock');
        Schema::dropIfExists('vehicle_assignments');
        Schema::dropIfExists('vehicles');
    }
};
