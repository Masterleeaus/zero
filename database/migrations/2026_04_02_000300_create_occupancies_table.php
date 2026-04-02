<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage A — Occupancy Model
 *
 * Tracks current and historical occupancy of a Unit.
 * Supports tenant history, lease intervals, and occupant tracking.
 *
 * Source: FacilityManagement/Entities/Occupancy.php +
 *         ManagedPremises/Database/Migrations/pm_property_units.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('occupancies', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('unit_id')->index();

            // Polymorphic occupant: customer | contact | user
            $table->string('occupant_type', 40)->default('customer')
                ->comment('customer | contact | user');
            $table->unsignedBigInteger('occupant_id')->nullable()->index();

            $table->string('occupancy_type', 40)->nullable()
                ->comment('residential | commercial | storage | temporary');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status', 30)->default('active')
                ->comment('active | vacated | pending | terminated');

            $table->string('contract_ref', 120)->nullable();
            $table->text('access_rights')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('premise_units')->onDelete('cascade');
            $table->index(['company_id', 'unit_id'], 'oc_company_unit');
            $table->index(['company_id', 'status'], 'oc_company_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupancies');
    }
};
