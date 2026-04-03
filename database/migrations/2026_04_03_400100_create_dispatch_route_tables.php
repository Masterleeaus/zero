<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Module 10 — fieldservice_route + fieldservice_route_availability
 *
 * Canonical route domain tables:
 *   dispatch_routes          — named routes (days of week, technician, capacity)
 *   dispatch_route_stops     — a concrete route-run for a date with ordered stops
 *   dispatch_route_stop_items — each schedulable entity stop on a day-route
 *   technician_availabilities — technician working hours / availability windows
 *   availability_windows      — individual recurring or one-off availability blocks
 *   route_blackout_days       — blocked dates (optionally scoped to postcode/zip)
 *   route_blackout_groups     — named groups of blackout days assigned to routes
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Dispatch Routes (named route templates) ────────────────────────────
        Schema::create('dispatch_routes', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');

            // Primary assigned technician (can be overridden per day-route run)
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();

            // Active days bitmask: bit 0 = Monday … bit 6 = Sunday
            $table->unsignedTinyInteger('active_days_mask')->default(0b0011111)
                ->comment('Bitmask: bit0=Mon,bit1=Tue,...,bit6=Sun. Default Mon–Fri.');

            // Capacity per day-run
            $table->unsignedSmallInteger('max_stops_per_day')->default(0)
                ->comment('0 = unlimited');

            // Territory / geo links
            $table->unsignedBigInteger('territory_id')->nullable()->index();
            $table->unsignedBigInteger('service_area_id')->nullable()->index();

            $table->string('status', 30)->default('active')
                ->comment('active | paused | archived');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'dr_company_status');
        });

        // ── Dispatch Route Stops (concrete run for a date) ────────────────────
        // Each row = one route running on one date (equivalent to fsm.route.dayroute)
        Schema::create('dispatch_route_stops', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('route_id')->index();
            $table->date('route_date');

            // Technician for this specific run (may differ from route default)
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();

            $table->string('status', 30)->default('draft')
                ->comment('draft | confirmed | in_progress | completed | cancelled');

            // Planned window
            $table->dateTime('planned_start_at')->nullable();
            $table->dateTime('planned_end_at')->nullable();

            // Actual window
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('actual_end_at')->nullable();

            // Geo tracking
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();

            // Capacity
            $table->unsignedSmallInteger('max_stops')->default(0)
                ->comment('0 = inherit from route');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('dispatch_routes')->onDelete('cascade');
            $table->index(['company_id', 'route_date'], 'drs_company_date');
            $table->index(['company_id', 'status'], 'drs_company_status');
            $table->unique(['route_id', 'route_date'], 'drs_route_date_unique');
        });

        // ── Dispatch Route Stop Items (ordered stops on a day-route) ──────────
        Schema::create('dispatch_route_stop_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('route_stop_id')->index();

            // Polymorphic link to the schedulable entity being stopped at
            // Supports: ServiceJob, ServicePlanVisit, InspectionInstance, ChecklistRun
            $table->string('schedulable_type', 100);
            $table->unsignedBigInteger('schedulable_id');

            // Ordering within the day-route
            $table->unsignedSmallInteger('sequence')->default(0);

            // Optional direct premises context (snapshot — avoids extra joins at dispatch time)
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // Estimated and actual visit times for this stop
            $table->dateTime('estimated_arrival_at')->nullable();
            $table->dateTime('actual_arrival_at')->nullable();
            $table->dateTime('actual_departure_at')->nullable();
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable();

            $table->string('status', 30)->default('pending')
                ->comment('pending | en_route | arrived | completed | skipped | failed');

            $table->text('dispatch_notes')->nullable();
            $table->timestamps();

            $table->foreign('route_stop_id')
                ->references('id')->on('dispatch_route_stops')
                ->onDelete('cascade');

            $table->index(['schedulable_type', 'schedulable_id'], 'drsi_schedulable');
            $table->index(['company_id', 'status'], 'drsi_company_status');
        });

        // ── Technician Availabilities (working schedule per user) ─────────────
        Schema::create('technician_availabilities', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();

            $table->string('name')->nullable()
                ->comment('Optional label, e.g. "Standard Working Hours"');

            // Active days bitmask: bit 0 = Monday … bit 6 = Sunday
            $table->unsignedTinyInteger('active_days_mask')->default(0b0011111);

            // Default working window (local time)
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');

            // Maximum work + overtime hours
            $table->decimal('max_work_hours', 5, 2)->default(8.0);
            $table->decimal('max_overtime_hours', 5, 2)->default(2.0);

            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'is_active'], 'ta_company_user_active');
        });

        // ── Availability Windows (individual blocks per technician) ────────────
        Schema::create('availability_windows', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('technician_availability_id')->nullable()->index();

            $table->string('window_type', 30)->default('available')
                ->comment('available | unavailable | leave | travel | break');

            // One-off window (nullable → means recurring weekday applies)
            $table->date('window_date')->nullable()->index();

            // Time range (local)
            $table->time('start_time');
            $table->time('end_time');

            // For recurring windows
            $table->boolean('is_recurring')->default(false);
            $table->unsignedTinyInteger('recurring_days_mask')->default(0)
                ->comment('Bitmask for recurring weekdays; 0 = not recurring');

            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'window_date'], 'aw_company_user_date');
        });

        // ── Route Blackout Days ────────────────────────────────────────────────
        Schema::create('route_blackout_days', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name')->nullable();
            $table->date('blackout_date');
            $table->string('zip', 20)->nullable()
                ->comment('When set, only blocks this postcode; otherwise blocks all');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('blackout_group_id')->nullable()->index();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'blackout_date'], 'rbd_company_date');
        });

        // ── Route Blackout Groups ─────────────────────────────────────────────
        Schema::create('route_blackout_groups', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        // ── Pivot: route ↔ blackout group ─────────────────────────────────────
        Schema::create('dispatch_route_blackout_group', static function (Blueprint $table) {
            $table->unsignedBigInteger('dispatch_route_id');
            $table->unsignedBigInteger('route_blackout_group_id');
            $table->primary(['dispatch_route_id', 'route_blackout_group_id'], 'drbg_pivot_primary');
        });

        // ── Foreign key back-fill for blackout_days ───────────────────────────
        Schema::table('route_blackout_days', static function (Blueprint $table) {
            $table->foreign('blackout_group_id')
                ->references('id')->on('route_blackout_groups')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_route_blackout_group');
        Schema::dropIfExists('route_blackout_days');
        Schema::dropIfExists('route_blackout_groups');
        Schema::dropIfExists('availability_windows');
        Schema::dropIfExists('technician_availabilities');
        Schema::dropIfExists('dispatch_route_stop_items');
        Schema::dropIfExists('dispatch_route_stops');
        Schema::dropIfExists('dispatch_routes');
    }
};
