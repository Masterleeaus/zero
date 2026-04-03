<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — Tenancy Alignment
 *
 * Adds company_id to tz_audit_log so that audit entries can be
 * scoped per tenant (Titan tenancy doctrine: company_id = tenant boundary).
 * Also adds subject_type + subject_id for generic polymorphic context.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tz_audit_log', 'company_id')) {
            Schema::table('tz_audit_log', static function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
            });
        }

        if (! Schema::hasColumn('tz_audit_log', 'subject_type')) {
            Schema::table('tz_audit_log', static function (Blueprint $table) {
                $table->string('subject_type', 100)->nullable()->after('company_id');
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
                $table->index(['subject_type', 'subject_id'], 'idx_tz_audit_log_subject');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tz_audit_log', static function (Blueprint $table) {
            $table->dropIndex('idx_tz_audit_log_subject');
            $table->dropColumn(['company_id', 'subject_type', 'subject_id']);
        });
    }
};
