<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage A — Facility Document System
 *
 * Polymorphic document attachment for Premises, Building, and Unit.
 * Supports: site_document, floorplan, compliance_doc, permit, safety_doc.
 *
 * Source: FacilityManagement/Entities/Doc.php
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('facility_documents', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            // Polymorphic: premises | building | unit
            $table->string('documentable_type', 60);
            $table->unsignedBigInteger('documentable_id');

            $table->string('doc_type', 60)->default('site_document')
                ->comment('site_document | floorplan | compliance_doc | permit | safety_doc | other');
            $table->string('title')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type', 80)->nullable();
            $table->unsignedInteger('file_size')->nullable()->comment('bytes');

            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->string('status', 30)->default('valid')
                ->comment('valid | expired | superseded | archived');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();

            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id'], 'fd_poly');
            $table->index(['company_id', 'doc_type'], 'fd_company_type');
            $table->index(['company_id', 'expires_at'], 'fd_company_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_documents');
    }
};
