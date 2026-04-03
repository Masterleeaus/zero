<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Chart of Accounts ────────────────────────────────────────────────────
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('gl_type', 20)->default('asset');  // asset|liability|equity|revenue|expense
            $table->string('type', 50)->default('cash');      // detail type (bank, cash, fixed_asset, etc.)
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('account_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('set null');
            $table->unique(['company_id', 'code']);
        });

        // ── Journal Entries ───────────────────────────────────────────────────────
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('reference', 100);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['company_id', 'reference']);
        });

        // ── Journal Lines ─────────────────────────────────────────────────────────
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->string('description', 255)->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // ── Ledger Transactions ───────────────────────────────────────────────────
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('type', 30);  // income|expense|transfer_in|transfer_out
            $table->decimal('amount', 15, 2);
            $table->string('category', 100)->nullable();
            $table->string('reference', 255)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('transaction_date');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
        });

        // ── Default Chart of Accounts Seed ───────────────────────────────────────
        // Seeded at the system level (no company_id) to serve as a template.
        // Per-company seeding is handled at company creation.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
