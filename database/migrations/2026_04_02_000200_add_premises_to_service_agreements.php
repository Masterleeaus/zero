<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage F — add premises_id to service_agreements.
 *
 * Allows an Agreement to be anchored to a canonical Premises record,
 * in addition to the legacy site_id field.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_agreements', static function (Blueprint $table) {
            $table->unsignedBigInteger('premises_id')
                ->nullable()
                ->after('site_id')
                ->index()
                ->comment('Canonical Premises linkage (replaces site_id over time)');

            $table->timestamp('expired_at')->nullable()->after('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_agreements', static function (Blueprint $table) {
            $table->dropColumn(['premises_id', 'expired_at']);
        });
    }
};
