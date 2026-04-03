<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance Domain Pass 2 — Accounts Payable Layer.
 *
 * Tables created / extended:
 *   - suppliers          (add default_account_id)
 *   - purchase_orders    (add service_job_id)
 *   - supplier_bills
 *   - supplier_bill_lines
 *   - supplier_payments
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Extend suppliers ────────────────────────────────────────────────
        if (Schema::hasTable('suppliers') && ! Schema::hasColumn('suppliers', 'default_account_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->unsignedBigInteger('default_account_id')->nullable()->after('payment_terms');
            });
        }

        // ── Extend purchase_orders ───────────────────────────────────────────
        if (Schema::hasTable('purchase_orders') && ! Schema::hasColumn('purchase_orders', 'service_job_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('service_job_id')->nullable()->index()->after('currency_code');
            });
        }

        // ── Supplier Bills ───────────────────────────────────────────────────
        if (! Schema::hasTable('supplier_bills')) {
            Schema::create('supplier_bills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
                $table->string('reference')->nullable();
                $table->date('bill_date');
                $table->date('due_date')->nullable();
                $table->string('currency', 10)->default('AUD');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_total', 14, 2)->default(0);
                $table->decimal('total', 14, 2)->default(0);
                $table->decimal('amount_paid', 14, 2)->default(0);
                $table->string('status')->default('draft'); // draft|awaiting_payment|partial|paid|overdue|void
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');
            });
        }

        // ── Supplier Bill Lines ──────────────────────────────────────────────
        if (! Schema::hasTable('supplier_bill_lines')) {
            Schema::create('supplier_bill_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_bill_id')->index();
                $table->unsignedBigInteger('account_id')->nullable()->index();
                $table->unsignedBigInteger('service_job_id')->nullable()->index();
                $table->string('description')->nullable();
                $table->decimal('amount', 14, 2)->default(0);
                $table->decimal('tax_rate', 8, 4)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->timestamps();

                $table->foreign('supplier_bill_id')->references('id')->on('supplier_bills')->onDelete('cascade');
            });
        }

        // ── Supplier Payments ────────────────────────────────────────────────
        if (! Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('supplier_bill_id')->index();
                $table->unsignedBigInteger('payment_account_id')->nullable()->index();
                $table->decimal('amount', 14, 2);
                $table->date('payment_date');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('supplier_bill_id')->references('id')->on('supplier_bills')->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_bill_lines');
        Schema::dropIfExists('supplier_bills');

        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'service_job_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('service_job_id');
            });
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'default_account_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('default_account_id');
            });
        }
    }
};
