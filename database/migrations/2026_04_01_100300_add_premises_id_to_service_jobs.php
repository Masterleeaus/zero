<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage C — Link Premises to Service domain
 *
 * Adds premises_id FK to service_jobs.
 * equipment.premises_id was already added in the equipment migration.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'premises_id')) {
                $table->unsignedBigInteger('premises_id')
                    ->nullable()
                    ->after('site_id')
                    ->comment('Structured premises hierarchy location');
                $table->index('premises_id');
                $table->foreign('premises_id')->references('id')->on('premises')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'premises_id')) {
                $table->dropForeign(['premises_id']);
                $table->dropIndex(['premises_id']);
                $table->dropColumn('premises_id');
            }
        });
    }
};
