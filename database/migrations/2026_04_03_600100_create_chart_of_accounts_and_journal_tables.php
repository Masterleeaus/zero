<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance Pass 1 — Chart of Accounts + Journal Engine
 *
 * Creates:
 *   - accounts         (chart-of-accounts entries)
 *   - journal_entries  (double-entry journal header)
 *   - journal_lines    (individual debit/credit lines)
 *
 * Does NOT touch existing money tables (quotes, invoices, payments, expenses …).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------ //
        // 1. Chart of Accounts
        // ------------------------------------------------------------------ //
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            $table->string('code', 20)->nullable();           // e.g. "1100"
            $table->string('name', 200);
            $table->string('type', 20);                       // asset|liability|equity|income|expense
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'code']);
        });

        // ------------------------------------------------------------------ //
        // 2. Journal Entry headers
        // ------------------------------------------------------------------ //
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->string('reference', 100)->nullable();     // human-readable ref
            $table->text('description')->nullable();
            $table->date('entry_date');
            $table->string('status', 20)->default('draft');   // draft|posted|void
            $table->string('currency', 10)->default('AUD');

            // Polymorphic link to originating document
            $table->nullableMorphs('source');                 // source_type, source_id

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'entry_date']);
        });

        // ------------------------------------------------------------------ //
        // 3. Journal Lines (individual debit / credit rows)
        // ------------------------------------------------------------------ //
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');

            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->timestamps();

            $table->foreign('journal_entry_id')
                ->references('id')->on('journal_entries')
                ->cascadeOnDelete();

            $table->foreign('account_id')
                ->references('id')->on('accounts')
                ->restrictOnDelete();

            $table->index(['journal_entry_id']);
            $table->index(['account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
