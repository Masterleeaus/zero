<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PWA Pass 3 — Offline artifact staging table.
 *
 * Stores staged offline field artifacts (proof photos, notes, document attachments)
 * received from PWA devices pending reconciliation into canonical job/proof systems.
 *
 * Artifact types: photo | note | proof | attachment | document
 * Stages:         pending | processing | reconciled | failed | abandoned
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_pwa_staged_artifacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('node_id', 64)->index();
            $table->string('client_ref', 128)->nullable()->comment('Client-supplied idempotency / local reference');
            $table->string('artifact_type', 30)->default('photo')->comment('photo|note|proof|attachment|document');
            $table->string('artifact_stage', 30)->default('pending')->comment('pending|processing|reconciled|failed|abandoned');

            // Linkage to business entities
            $table->string('job_id', 64)->nullable()->index()->comment('Linked job/work order ID');
            $table->string('process_id', 64)->nullable()->index()->comment('Linked process/task ID');
            $table->string('signal_ref', 128)->nullable()->comment('Originating signal idempotency_key');

            // Artifact content / metadata
            $table->json('artifact_meta')->nullable()->comment('Dimensions, mime, filesize, local URL, capture timestamp etc');
            $table->text('note_body')->nullable()->comment('For note-type artifacts');
            $table->string('filename', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            // Upload state
            $table->boolean('upload_attempted')->default(false);
            $table->boolean('upload_complete')->default(false);
            $table->string('upload_url', 1024)->nullable()->comment('Temporary or permanent URL after upload');

            // Reconciliation
            $table->unsignedBigInteger('reconciled_to_id')->nullable()->comment('Foreign ID in canonical proof/media/note table');
            $table->string('reconciled_to_type', 100)->nullable()->comment('Morph type for reconciled record');
            $table->timestamp('reconciled_at')->nullable();

            // Error tracking
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->string('failure_reason', 500)->nullable();
            $table->timestamp('last_attempted_at')->nullable();

            // Timestamps
            $table->timestamp('client_captured_at')->nullable()->comment('Client-side capture timestamp');
            $table->timestamps();

            $table->unique(['node_id', 'client_ref'], 'uq_pwa_staged_artifact_ref');
            $table->index(['company_id', 'artifact_stage']);
            $table->index(['company_id', 'artifact_type']);
            $table->index(['artifact_stage', 'retry_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_pwa_staged_artifacts');
    }
};
