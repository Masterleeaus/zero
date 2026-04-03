<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Module 8 — Equipment Warranty Integration
 *
 * Stage A/B/C/D/E:
 *   1. Extend equipment, installed_equipment, site_assets with full warranty fields
 *   2. Create equipment_warranties table (EquipmentWarranty)
 *   3. Create warranty_claims table (WarrantyClaim)
 *   4. Extend service_jobs with warranty linkage columns
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Extend equipment ──────────────────────────────────────────────────
        Schema::table('equipment', static function (Blueprint $table) {
            $table->date('warranty_start_date')->nullable()->after('warranty_expiry');
            $table->string('warranty_provider')->nullable()->after('warranty_start_date');
            $table->string('warranty_reference')->nullable()->after('warranty_provider');
            $table->string('coverage_type', 40)->nullable()->after('warranty_reference')
                ->comment('parts | labour | full | limited | extended');
            $table->text('coverage_notes')->nullable()->after('coverage_type');
            $table->date('claimable_until')->nullable()->after('coverage_notes');
            $table->boolean('extended_warranty_flag')->default(false)->after('claimable_until');
            $table->string('warranty_status', 30)->nullable()->after('extended_warranty_flag')
                ->comment('active | expired | expiring_soon | void | claimed | unknown');
        });

        // ── Extend installed_equipment ────────────────────────────────────────
        Schema::table('installed_equipment', static function (Blueprint $table) {
            $table->date('warranty_start_date')->nullable()->after('notes');
            $table->date('warranty_expiry')->nullable()->after('warranty_start_date');
            $table->string('warranty_provider')->nullable()->after('warranty_expiry');
            $table->string('warranty_reference')->nullable()->after('warranty_provider');
            $table->string('coverage_type', 40)->nullable()->after('warranty_reference')
                ->comment('parts | labour | full | limited | extended');
            $table->text('coverage_notes')->nullable()->after('coverage_type');
            $table->date('claimable_until')->nullable()->after('coverage_notes');
            $table->boolean('extended_warranty_flag')->default(false)->after('claimable_until');
            $table->string('warranty_status', 30)->nullable()->after('extended_warranty_flag')
                ->comment('active | expired | expiring_soon | void | claimed | unknown');
        });

        // ── Extend site_assets ────────────────────────────────────────────────
        Schema::table('site_assets', static function (Blueprint $table) {
            $table->date('warranty_start_date')->nullable()->after('warranty_expiry');
            $table->string('warranty_provider')->nullable()->after('warranty_start_date');
            $table->string('warranty_reference')->nullable()->after('warranty_provider');
            $table->string('coverage_type', 40)->nullable()->after('warranty_reference')
                ->comment('parts | labour | full | limited | extended');
            $table->text('coverage_notes')->nullable()->after('coverage_type');
            $table->date('claimable_until')->nullable()->after('coverage_notes');
            $table->boolean('extended_warranty_flag')->default(false)->after('claimable_until');
            $table->string('warranty_status', 30)->nullable()->after('extended_warranty_flag')
                ->comment('active | expired | expiring_soon | void | claimed | unknown');
        });

        // ── Create equipment_warranties ───────────────────────────────────────
        Schema::create('equipment_warranties', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            // Primary host entity (at least one must be set)
            $table->unsignedBigInteger('installed_equipment_id')->nullable()->index();
            $table->unsignedBigInteger('equipment_id')->nullable()->index();
            $table->unsignedBigInteger('site_asset_id')->nullable()->index();

            // Agreement linkage (optional)
            $table->unsignedBigInteger('agreement_id')->nullable()->index();

            // Warranty details
            $table->string('name')->comment('Descriptive name for this warranty record');
            $table->string('warranty_provider')->nullable();
            $table->string('warranty_reference')->nullable();
            $table->string('coverage_type', 40)->nullable()
                ->comment('parts | labour | full | limited | extended');
            $table->text('coverage_notes')->nullable();

            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('claimable_until')->nullable();

            $table->boolean('extended_warranty_flag')->default(false);
            $table->string('warranty_status', 30)->default('unknown')
                ->comment('active | expired | expiring_soon | void | claimed | unknown');

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'warranty_status'], 'ew_company_status');
            $table->index(['company_id', 'warranty_expiry'], 'ew_company_expiry');
        });

        // ── Create warranty_claims ────────────────────────────────────────────
        Schema::create('warranty_claims', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('equipment_warranty_id')->index();
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();

            $table->string('claim_reference')->nullable();
            $table->date('claim_date')->nullable();
            $table->string('provider')->nullable();
            $table->string('status', 30)->default('draft')
                ->comment('draft | submitted | approved | rejected | completed | cancelled');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('approved_flag')->default(false);
            $table->boolean('rejected_flag')->default(false);

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_warranty_id')
                ->references('id')->on('equipment_warranties')->onDelete('cascade');

            $table->index(['company_id', 'status'], 'wc_company_status');
        });

        // ── Extend service_jobs with warranty linkage ─────────────────────────
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->boolean('is_warranty_job')->default(false)->after('is_billable');
            $table->unsignedBigInteger('warranty_claim_id')->nullable()->after('is_warranty_job')->index();
            $table->unsignedBigInteger('covered_equipment_id')->nullable()->after('warranty_claim_id')->index()
                ->comment('FK to installed_equipment.id for warranty-covered equipment');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            $table->dropColumn(['is_warranty_job', 'warranty_claim_id', 'covered_equipment_id']);
        });

        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('equipment_warranties');

        Schema::table('site_assets', static function (Blueprint $table) {
            $table->dropColumn([
                'warranty_start_date', 'warranty_provider', 'warranty_reference',
                'coverage_type', 'coverage_notes', 'claimable_until',
                'extended_warranty_flag', 'warranty_status',
            ]);
        });

        Schema::table('installed_equipment', static function (Blueprint $table) {
            $table->dropColumn([
                'warranty_start_date', 'warranty_expiry', 'warranty_provider', 'warranty_reference',
                'coverage_type', 'coverage_notes', 'claimable_until',
                'extended_warranty_flag', 'warranty_status',
            ]);
        });

        Schema::table('equipment', static function (Blueprint $table) {
            $table->dropColumn([
                'warranty_start_date', 'warranty_provider', 'warranty_reference',
                'coverage_type', 'coverage_notes', 'claimable_until',
                'extended_warranty_flag', 'warranty_status',
            ]);
        });
    }
};
