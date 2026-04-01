<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 4 — fieldservice_activity
 *
 * Creates the job_activities table, which tracks both:
 *  - Template-level activity definitions (template_id set, service_job_id null)
 *  - Job-level activity instances copied from a template or added ad-hoc
 *    (service_job_id set)
 *
 * Mirrors the Odoo fsm.activity model, adapted to the host architecture:
 *  - company_id tenancy on every row
 *  - state machine: todo → done / cancel
 *  - required flag blocks job closure until completed
 *  - completed_by / completed_on audit trail
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_activities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->index();

            // Polymorphic owner: either a live job or a template definition
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('template_id')->nullable()->index();

            $table->string('name');
            $table->string('ref')->nullable();
            $table->unsignedSmallInteger('sequence')->default(0);

            $table->boolean('required')->default(false);
            $table->boolean('completed')->default(false);

            // State machine: todo (default), done, cancel
            $table->string('state', 20)->default('todo');

            // Completion audit
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_on')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('service_job_id')
                ->references('id')->on('service_jobs')
                ->onDelete('cascade');

            $table->foreign('template_id')
                ->references('id')->on('job_templates')
                ->onDelete('cascade');

            $table->foreign('completed_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Composite performance indexes
            $table->index(['company_id', 'service_job_id', 'state'], 'ja_job_state');
            $table->index(['company_id', 'state', 'required'], 'ja_required_todo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_activities');
    }
};
