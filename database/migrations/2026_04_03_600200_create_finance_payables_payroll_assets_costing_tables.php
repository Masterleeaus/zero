<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance Pass 2 — Payables + Payroll + Financial Assets + Job Costing
 *
 * Creates:
 *   - supplier_bills          (AP documents — bridges Inventory/Supplier)
 *   - supplier_bill_items     (line items on supplier bills)
 *   - payrolls                (payroll run headers)
 *   - payroll_lines           (per-employee lines — bridges Work/StaffProfile)
 *   - financial_assets        (capital asset register)
 *   - job_cost_entries        (cost captured against service jobs)
 *
 * Does NOT touch existing host tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------ //
        // 1. Supplier Bills (Accounts Payable)
        // ------------------------------------------------------------------ //
        Schema::create('supplier_bills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable()->index();
            $table->unsignedBigInteger('purchase_order_id')->nullable()->index();

            $table->string('bill_number', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft|approved|paid|cancelled

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('currency', 10)->default('AUD');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'due_date']);
            $table->index(['company_id', 'supplier_id']);
        });

        // ------------------------------------------------------------------ //
        // 2. Supplier Bill Items
        // ------------------------------------------------------------------ //
        Schema::create('supplier_bill_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('supplier_bill_id')->index();
            $table->unsignedBigInteger('account_id')->nullable();

            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);

            $table->timestamps();
        });

        // ------------------------------------------------------------------ //
        // 3. Payroll Runs
        // ------------------------------------------------------------------ //
        Schema::create('payrolls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->string('reference', 100)->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            $table->string('status', 20)->default('draft'); // draft|processing|approved|paid|cancelled
            $table->string('currency', 10)->default('AUD');

            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'period_start', 'period_end']);
        });

        // ------------------------------------------------------------------ //
        // 4. Payroll Lines
        // ------------------------------------------------------------------ //
        Schema::create('payroll_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('payroll_id')->index();
            $table->unsignedBigInteger('staff_profile_id')->nullable()->index();
            $table->unsignedBigInteger('timesheet_submission_id')->nullable();

            $table->string('employee_name', 200)->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('gross_pay', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // ------------------------------------------------------------------ //
        // 5. Financial Assets (Capital Asset Register)
        // ------------------------------------------------------------------ //
        Schema::create('financial_assets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('depreciation_rate', 8, 4)->default(0);   // annual rate e.g. 0.2 = 20%
            $table->string('depreciation_method', 50)->default('straight_line');
            $table->string('status', 20)->default('active');           // active|disposed
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        // ------------------------------------------------------------------ //
        // 6. Job Cost Entries (Job Costing Hooks)
        // ------------------------------------------------------------------ //
        Schema::create('job_cost_entries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();

            $table->string('cost_type', 30)->default('labour'); // labour|material|equipment|subcontractor|overhead
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('cost_date');
            $table->string('reference', 100)->nullable();

            // Polymorphic source (Expense, SupplierBill, TimesheetSubmission, etc.)
            $table->nullableMorphs('source');

            $table->timestamps();

            $table->index(['company_id', 'cost_type']);
            $table->index(['company_id', 'cost_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_cost_entries');
        Schema::dropIfExists('financial_assets');
        Schema::dropIfExists('payroll_lines');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('supplier_bill_items');
        Schema::dropIfExists('supplier_bills');
    }
};
