<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage B+ — Checklist Framework
 *
 * Reusable checklist system attachable to ServiceJob, InspectionInstance,
 * or Premises.  Extends the existing simple Checklist model by adding
 * template-driven runs with structured item responses.
 *
 *   checklist_templates  — named reusable templates
 *   checklist_items      — template line items
 *   checklist_runs       — a template executed in context
 *   checklist_responses  — per-item responses within a run
 *
 * Source: ManagedPremises/Entities/PropertyChecklist.php,
 *         SiteInspection/Entities/RecurringScheduleItems.php.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Checklist Templates ───────────────────────────────────────────────
        Schema::create('checklist_templates', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('category', 60)->nullable()
                ->comment('e.g. safety | hygiene | inspection | handover');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'category'], 'ct_company_category');
        });

        // ── Checklist Template Items ──────────────────────────────────────────
        Schema::create('checklist_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('checklist_template_id')->index();

            $table->string('label');
            $table->string('response_type', 30)->default('pass_fail')
                ->comment('pass_fail | checkbox | numeric | text | photo_required | signature_required | notes');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('guidance')->nullable();

            $table->timestamps();

            $table->foreign('checklist_template_id')
                ->references('id')->on('checklist_templates')->onDelete('cascade');
        });

        // ── Checklist Runs (extend existing table from Stage J) ───────────────
        Schema::table('checklist_runs', static function (Blueprint $table) {
            if (! Schema::hasColumn('checklist_runs', 'checklist_template_id')) {
                $table->unsignedBigInteger('checklist_template_id')->nullable()->index()->after('company_id');
            }
            if (! Schema::hasColumn('checklist_runs', 'runnable_type')) {
                $table->string('runnable_type', 60)->nullable()->after('checklist_template_id');
            }
            if (! Schema::hasColumn('checklist_runs', 'runnable_id')) {
                $table->unsignedBigInteger('runnable_id')->nullable()->after('runnable_type');
            }
            if (! Schema::hasColumn('checklist_runs', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->index()->after('status');
            }
            if (! Schema::hasColumn('checklist_runs', 'notes')) {
                $table->text('notes')->nullable()->after('assigned_to');
            }
        });

        // ── Checklist Responses ───────────────────────────────────────────────
        Schema::create('checklist_responses', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('checklist_run_id')->index();
            $table->unsignedBigInteger('checklist_item_id')->index();

            $table->string('result', 30)->nullable()
                ->comment('pass | fail | na');
            $table->boolean('is_checked')->default(false);
            $table->decimal('numeric_value', 12, 3)->nullable();
            $table->text('text_value')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('photo_required')->default(false);
            $table->string('photo_path')->nullable();
            $table->boolean('signature_captured')->default(false);

            $table->unsignedBigInteger('responded_by')->nullable()->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('checklist_run_id')
                ->references('id')->on('checklist_runs')->onDelete('cascade');
            $table->foreign('checklist_item_id')
                ->references('id')->on('checklist_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_responses');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklist_templates');

        // Remove extended columns from checklist_runs (do not drop the table itself)
        Schema::table('checklist_runs', static function (Blueprint $table) {
            foreach (['checklist_template_id', 'runnable_type', 'runnable_id', 'assigned_to', 'notes'] as $col) {
                if (Schema::hasColumn('checklist_runs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
