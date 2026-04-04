<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 08 — DocsExecutionBridge
 *
 * Extends facility_documents with injection metadata and creates the
 * three pivot / rule tables that drive procedure injection at execution time.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Extend facility_documents ─────────────────────────────────────
        Schema::table('facility_documents', static function (Blueprint $table) {
            if (! Schema::hasColumn('facility_documents', 'document_category')) {
                $table->string('document_category', 32)->nullable()->after('doc_type')
                    ->comment('procedure|safety|compliance|regulatory|handover|sop|msds|permit');
            }
            if (! Schema::hasColumn('facility_documents', 'applies_to_asset_types')) {
                $table->json('applies_to_asset_types')->nullable()->after('document_category');
            }
            if (! Schema::hasColumn('facility_documents', 'applies_to_job_types')) {
                $table->json('applies_to_job_types')->nullable()->after('applies_to_asset_types');
            }
            if (! Schema::hasColumn('facility_documents', 'applies_to_service_types')) {
                $table->json('applies_to_service_types')->nullable()->after('applies_to_job_types');
            }
            if (! Schema::hasColumn('facility_documents', 'access_level_minimum')) {
                $table->unsignedTinyInteger('access_level_minimum')->nullable()->after('applies_to_service_types');
            }
            if (! Schema::hasColumn('facility_documents', 'requires_certification')) {
                $table->string('requires_certification', 128)->nullable()->after('access_level_minimum');
            }
            if (! Schema::hasColumn('facility_documents', 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(false)->after('requires_certification');
            }
            if (! Schema::hasColumn('facility_documents', 'version')) {
                $table->string('version', 32)->nullable()->after('is_mandatory');
            }
            if (! Schema::hasColumn('facility_documents', 'supersedes_id')) {
                $table->unsignedBigInteger('supersedes_id')->nullable()->after('version');
                $table->foreign('supersedes_id')
                    ->references('id')
                    ->on('facility_documents')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('facility_documents', 'review_due_at')) {
                $table->date('review_due_at')->nullable()->after('supersedes_id');
            }
            if (! Schema::hasColumn('facility_documents', 'embedding_vector')) {
                $table->json('embedding_vector')->nullable()->after('review_due_at');
            }
        });

        // ── 2. job_injected_documents ─────────────────────────────────────────
        if (! Schema::hasTable('job_injected_documents')) {
            Schema::create('job_injected_documents', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('document_id')->index();
                $table->string('injection_source', 32)->default('rule')
                    ->comment('rule|ai_relevance|manual');
                $table->decimal('relevance_score', 5, 4)->nullable();
                $table->timestamp('injected_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->timestamps();

                $table->foreign('job_id')
                    ->references('id')->on('service_jobs')->cascadeOnDelete();
                $table->foreign('document_id')
                    ->references('id')->on('facility_documents')->cascadeOnDelete();
                $table->foreign('acknowledged_by')
                    ->references('id')->on('users')->nullOnDelete();

                $table->unique(['job_id', 'document_id']);
            });
        }

        // ── 3. inspection_injected_documents ──────────────────────────────────
        if (! Schema::hasTable('inspection_injected_documents')) {
            Schema::create('inspection_injected_documents', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inspection_instance_id')->index();
                $table->unsignedBigInteger('document_id')->index();
                $table->string('injection_source', 32)->default('rule')
                    ->comment('rule|ai_relevance|manual');
                $table->decimal('relevance_score', 5, 4)->nullable();
                $table->timestamp('injected_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->timestamps();

                $table->foreign('inspection_instance_id')
                    ->references('id')->on('inspection_instances')->cascadeOnDelete();
                $table->foreign('document_id')
                    ->references('id')->on('facility_documents')->cascadeOnDelete();
                $table->foreign('acknowledged_by')
                    ->references('id')->on('users')->nullOnDelete();

                $table->unique(['inspection_instance_id', 'document_id']);
            });
        }

        // ── 4. document_injection_rules ───────────────────────────────────────
        if (! Schema::hasTable('document_injection_rules')) {
            Schema::create('document_injection_rules', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('rule_type', 32)
                    ->comment('job_type|asset_type|service_type|access_level|premises_zone');
                $table->string('rule_value', 255);
                $table->unsignedBigInteger('document_id')->index();
                $table->boolean('is_mandatory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('company_id')
                    ->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('document_id')
                    ->references('id')->on('facility_documents')->cascadeOnDelete();

                $table->index(['company_id', 'rule_type', 'rule_value']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_injection_rules');
        Schema::dropIfExists('inspection_injected_documents');
        Schema::dropIfExists('job_injected_documents');

        Schema::table('facility_documents', static function (Blueprint $table) {
            $columns = [
                'document_category',
                'applies_to_asset_types',
                'applies_to_job_types',
                'applies_to_service_types',
                'access_level_minimum',
                'requires_certification',
                'is_mandatory',
                'version',
                'supersedes_id',
                'review_due_at',
                'embedding_vector',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('facility_documents', $column)) {
                    if ($column === 'supersedes_id') {
                        $table->dropForeign(['supersedes_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
