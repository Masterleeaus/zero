<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage I — SiteAsset domain table.
 *
 * SiteAsset represents installed infrastructure at a premises:
 * fire panels, sprinkler systems, HVAC ductwork, lifts, fixed plant etc.
 *
 * Distinct from Equipment (serialised movable devices).
 *
 * Status values: active | decommissioned | under_maintenance | pending_inspection
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_assets', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('asset_code')->nullable()->index();
            $table->string('category')->nullable()->index()
                ->comment('e.g. fire_panel | sprinkler | hvac | lift | electrical | plumbing');
            $table->string('status', 40)->default('active')
                ->comment('active | decommissioned | under_maintenance | pending_inspection');

            // Location hierarchy
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('building_id')->nullable()->index();
            $table->unsignedBigInteger('floor_id')->nullable()->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();

            // Customer linkage
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // Service context
            $table->unsignedBigInteger('agreement_id')->nullable()->index();

            // Asset metadata
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->date('install_date')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('location_description')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status'], 'sa_company_status');
            $table->index(['company_id', 'premises_id'], 'sa_company_premises');
            $table->index(['company_id', 'category'], 'sa_company_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_assets');
    }
};
