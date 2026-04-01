<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage C — Premises Foundation
 *
 * Extracted and adapted from CodeToUse/managed-premises/ManagedPremises and Units.
 *
 * Hierarchy:
 *   Premises → Building → Floor → Unit → Room
 *
 * All tables include company_id for tenancy.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Premises (top-level site intelligence) ────────────────────────────
        Schema::create('premises', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('premises_code')->nullable()->index();
            $table->string('type', 30)->default('commercial')
                ->comment('commercial | residential | strata | industrial');
            $table->string('status', 30)->default('active')->index();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('suburb')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Site context memory (adapted from pm_properties)
            $table->longText('access_notes')->nullable();
            $table->longText('hazards')->nullable();
            $table->string('parking_notes')->nullable();
            $table->string('lockbox_code')->nullable();
            $table->string('keys_location')->nullable();
            $table->time('service_window_start')->nullable();
            $table->time('service_window_end')->nullable();

            // Customer linkage
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status'], 'pr_company_status');
            $table->index(['company_id', 'customer_id'], 'pr_company_customer');
        });

        // ── Buildings (within a Premises) ─────────────────────────────────────
        Schema::create('buildings', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('premises_id')->index();

            $table->string('name');
            $table->string('building_code')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('premises_id')->references('id')->on('premises')->onDelete('cascade');
            $table->index(['company_id', 'premises_id'], 'bu_company_premises');
        });

        // ── Floors (within a Building) ────────────────────────────────────────
        Schema::create('premise_floors', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('building_id')->index();

            $table->string('name');
            $table->string('floor_code')->nullable();
            $table->unsignedSmallInteger('level_number')->default(0);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->index(['company_id', 'building_id'], 'fl_company_building');
        });

        // ── Units (within a Floor) ────────────────────────────────────────────
        Schema::create('premise_units', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('floor_id')->index();

            $table->string('name');
            $table->string('unit_code')->nullable();
            $table->string('status', 30)->default('active');
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('floor_id')->references('id')->on('premise_floors')->onDelete('cascade');
            $table->index(['company_id', 'floor_id'], 'un_company_floor');
        });

        // ── Rooms (within a Unit) ─────────────────────────────────────────────
        Schema::create('rooms', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('unit_id')->index();

            $table->string('name');
            $table->string('room_code')->nullable();
            $table->string('type', 30)->nullable()
                ->comment('e.g. bedroom | bathroom | kitchen | office | storage');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('premise_units')->onDelete('cascade');
            $table->index(['company_id', 'unit_id'], 'rm_company_unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('premise_units');
        Schema::dropIfExists('premise_floors');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('premises');
    }
};
