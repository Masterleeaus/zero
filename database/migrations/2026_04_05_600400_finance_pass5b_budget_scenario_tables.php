<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('company_id');
        });

        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('cost_bucket')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('line_type', ['revenue', 'expense', 'labor', 'materials', 'overhead', 'capex', 'liability'])->default('expense');
            $table->string('scenario_tag')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('budget_id');
        });

        Schema::create('financial_action_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('action_type');
            $table->string('title');
            $table->text('summary');
            $table->text('reason');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('confidence', 5, 2)->default(75.00);
            $table->string('source_service')->default('system');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->json('payload')->nullable();
            $table->enum('status', ['pending_review', 'approved', 'rejected', 'dismissed'])->default('pending_review');
            $table->boolean('created_by_system')->default(true);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->index('company_id');
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_action_recommendations');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
    }
};
