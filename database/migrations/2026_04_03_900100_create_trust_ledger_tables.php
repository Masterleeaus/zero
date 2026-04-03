<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── trust_ledger_entries ─────────────────────────────────────────────
        Schema::create('trust_ledger_entries', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('chain_hash', 64)->unique();
            $table->string('parent_hash', 64)->nullable()->index();
            $table->string('entry_type', 60)->index();
            $table->string('subject_type', 120);
            $table->unsignedBigInteger('subject_id');
            $table->string('actor_type', 30)->default('user'); // user | system | ai
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('payload');
            $table->timestamp('signed_at');
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — this table is immutable.

            $table->index(['subject_type', 'subject_id']);
            $table->index(['company_id', 'entry_type']);
        });

        // ── trust_evidence_attachments ───────────────────────────────────────
        Schema::create('trust_evidence_attachments', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ledger_entry_id')->index();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum', 64);   // SHA-256 hex
            $table->string('attachment_type', 60)->default('photo');
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->foreign('ledger_entry_id')
                ->references('id')
                ->on('trust_ledger_entries')
                ->onDelete('cascade');
        });

        // ── trust_chain_seals ────────────────────────────────────────────────
        Schema::create('trust_chain_seals', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->timestamp('sealed_at');
            $table->unsignedInteger('entry_count')->default(0);
            $table->string('root_hash', 64);
            $table->string('seal_hash', 64)->unique();
            $table->unsignedBigInteger('sealed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_chain_seals');
        Schema::dropIfExists('trust_evidence_attachments');
        Schema::dropIfExists('trust_ledger_entries');
    }
};
