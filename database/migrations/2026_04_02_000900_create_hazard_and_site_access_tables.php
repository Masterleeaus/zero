<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage G + H — Hazard Memory System & Site Access Profile
 *
 * Stage G: Structured hazard records per premises / unit.
 *   hazards — replaces the free-text hazards field on premises.
 *
 * Stage H: Structured site access memory.
 *   site_access_profiles — keys, alarm codes, entry instructions, contacts.
 *
 * Sources: ManagedPremises/Entities/PropertyHazard.php,
 *          ManagedPremises/Entities/PropertyKey.php,
 *          FacilityManagement dispatch context.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Hazards ───────────────────────────────────────────────────────────
        Schema::create('hazards', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            // Optional job that identified / was associated with this hazard
            $table->unsignedBigInteger('service_job_id')->nullable()->index();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('severity', 30)->default('medium')
                ->comment('low | medium | high | critical');

            $table->text('instructions')->nullable()
                ->comment('Safety instructions for staff attending site');
            $table->text('ppe_required')->nullable()
                ->comment('PPE items required: e.g. gloves, mask, hard hat');
            $table->boolean('restricted_access')->default(false);

            $table->string('status', 30)->default('active')
                ->comment('active | resolved | monitoring');

            $table->date('identified_at')->nullable();
            $table->date('resolved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'severity'], 'hz_company_severity');
            $table->index(['company_id', 'status'], 'hz_company_status');
        });

        // ── Site Access Profiles ──────────────────────────────────────────────
        Schema::create('site_access_profiles', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('premises_id')->index();

            // Key information
            $table->string('key_type', 60)->nullable()
                ->comment('physical_key | lockbox | fob | keycard | combination');
            $table->string('key_location')->nullable();
            $table->string('key_reference', 120)->nullable();

            // Entry instructions
            $table->text('entry_instructions')->nullable();
            $table->string('lockbox_code', 120)->nullable();
            $table->string('alarm_code', 120)->nullable();
            $table->text('alarm_instructions')->nullable();

            // Parking / vehicle access
            $table->text('parking_notes')->nullable();

            // Primary contact (quick reference for dispatch)
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_role', 60)->nullable()
                ->comment('e.g. site_manager | caretaker | building_manager | owner');

            // After-hours contact
            $table->string('afterhours_contact_name')->nullable();
            $table->string('afterhours_contact_phone')->nullable();

            $table->text('additional_notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('premises_id')->references('id')->on('premises')->onDelete('cascade');
            $table->index(['company_id', 'premises_id'], 'sap_company_premises');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_access_profiles');
        Schema::dropIfExists('hazards');
    }
};
