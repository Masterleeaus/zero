<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Extend expenses table ──────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('cost_bucket')->nullable()->after('description');
            $table->unsignedBigInteger('service_job_id')->nullable()->after('cost_bucket');
            $table->unsignedBigInteger('site_id')->nullable()->after('service_job_id');
            $table->unsignedBigInteger('team_id')->nullable()->after('site_id');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('team_id');
            $table->boolean('reimbursable_to_customer')->default(false)->after('supplier_id');
            $table->string('allocation_reference')->nullable()->after('reimbursable_to_customer');

            $table->foreign('service_job_id')->references('id')->on('service_jobs')->nullOnDelete();
        });

        // ── job_cost_allocations ───────────────────────────────────────────────
        Schema::create('job_cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('service_job_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('cost_type');
            $table->decimal('amount', 12, 2);
            $table->decimal('quantity', 10, 4)->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('allocated_at');
            $table->boolean('posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'service_job_id', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_cost_allocations');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['service_job_id']);
            $table->dropColumn([
                'cost_bucket',
                'service_job_id',
                'site_id',
                'team_id',
                'supplier_id',
                'reimbursable_to_customer',
                'allocation_reference',
            ]);
        });
    }
};
