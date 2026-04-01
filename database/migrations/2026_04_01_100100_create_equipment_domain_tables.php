<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage B — FSM Module 7 (fieldservice_equipment_stock)
 *
 * Creates the Equipment domain tables:
 *   equipment             — serialised equipment catalogue
 *   installed_equipment   — site-installed device tracking
 *   equipment_movements   — stock / assignment movement log
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Master equipment catalogue ────────────────────────────────────────
        Schema::create('equipment', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('category')->nullable()->index()->comment('e.g. hvac, pump, sensor');
            $table->string('status', 30)->default('in_stock')
                ->comment('in_stock | installed | removed | retired | lost');

            // Linkages
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index()
                ->comment('Populated after Premises domain is created');
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('agreement_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();

            // Serialised fields
            $table->date('install_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status'], 'eq_company_status');
            $table->index(['company_id', 'serial_number'], 'eq_company_serial');
        });

        // ── Site-installed device tracking ────────────────────────────────────
        Schema::create('installed_equipment', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('equipment_id')->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('service_job_id')->nullable()->index()
                ->comment('Job that performed the installation');

            $table->date('installed_at')->nullable();
            $table->date('removed_at')->nullable();
            $table->string('status', 30)->default('active')
                ->comment('active | removed | replaced');
            $table->text('location_description')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->index(['company_id', 'status'], 'ie_company_status');
        });

        // ── Equipment movement / stock log ────────────────────────────────────
        Schema::create('equipment_movements', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('equipment_id')->index();
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();

            $table->string('movement_type', 40)
                ->comment('installed|removed|replaced|consumed|assigned_to_site|assigned_to_job');
            $table->text('notes')->nullable();
            $table->timestamp('moved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->index(['company_id', 'movement_type'], 'em_company_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_movements');
        Schema::dropIfExists('installed_equipment');
        Schema::dropIfExists('equipment');
    }
};
