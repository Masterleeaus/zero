<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage D — Meter Domain
 *
 * Utility metering: electricity, water, gas, HVAC runtime, custom counters.
 * Supports threshold alerts and anomaly detection fields.
 *
 *   meters          — meter registry per premises / unit
 *   meter_readings  — timestamped reading log
 *
 * Sources: FacilityManagement/Entities/Meter.php + MeterRead.php,
 *          ManagedPremises/Database/Migrations/pm_meter_readings.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Meters ────────────────────────────────────────────────────────────
        Schema::create('meters', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();

            // Optional link to a SiteAsset that IS the meter device
            $table->unsignedBigInteger('site_asset_id')->nullable()->index();

            $table->string('meter_type', 40)->default('water')
                ->comment('water | electricity | gas | hvac_runtime | solar | custom');
            $table->string('name')->nullable();
            $table->string('barcode', 120)->nullable();
            $table->string('unit_of_measure', 20)->nullable()
                ->comment('e.g. kWh | L | m³ | h');

            // Alert thresholds
            $table->decimal('threshold_high', 12, 3)->nullable();
            $table->decimal('threshold_low', 12, 3)->nullable();
            $table->unsignedSmallInteger('expected_interval_days')->nullable()
                ->comment('Expected days between readings');

            // Running state
            $table->decimal('last_reading', 12, 3)->nullable();
            $table->timestamp('last_read_at')->nullable();

            $table->string('status', 30)->default('active')
                ->comment('active | inactive | replaced | removed');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'meter_type'], 'me_company_type');
            $table->index(['company_id', 'premises_id'], 'me_company_premises');
        });

        // ── Meter Readings ─────────────────────────────────────────────────
        Schema::create('meter_readings', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('meter_id')->index();

            $table->decimal('reading', 12, 3);
            $table->decimal('consumed', 12, 3)->nullable()
                ->comment('Computed delta since previous reading');
            $table->decimal('rate', 12, 4)->nullable();
            $table->decimal('amount', 12, 2)->nullable();

            $table->date('reading_date')->index();
            $table->timestamp('read_at')->nullable();

            $table->string('source', 30)->default('manual')
                ->comment('manual | auto | import | estimate');

            // Anomaly flag — set automatically when threshold exceeded
            $table->boolean('anomaly_flagged')->default(false);
            $table->string('anomaly_reason', 100)->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reader_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('meter_id')->references('id')->on('meters')->onDelete('cascade');
            $table->index(['meter_id', 'reading_date'], 'mr_meter_date');
            $table->index(['company_id', 'anomaly_flagged'], 'mr_anomaly');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('meters');
    }
};
