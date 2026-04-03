<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // fieldservice_portal: add portal_visible to job_stages
        Schema::table('job_stages', function (Blueprint $table) {
            $table->boolean('portal_visible')->default(true)->after('require_signature');
        });

        // fieldservice_project: create field_service_projects table
        Schema::create('field_service_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name');
            $table->string('reference')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active|on_hold|completed|cancelled
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->date('planned_start')->nullable();
            $table->date('planned_end')->nullable();
            $table->date('actual_start')->nullable();
            $table->date('actual_end')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });

        // fieldservice_project: add project_id and project_task_ref to service_jobs
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('deal_id')->index();
            $table->string('project_task_ref')->nullable()->after('project_id');

            $table->foreign('project_id')->references('id')->on('field_service_projects')->onDelete('set null');
        });

        // fieldservice_project: add project_id to service_plan_visits
        Schema::table('service_plan_visits', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('service_job_id')->index();

            $table->foreign('project_id')->references('id')->on('field_service_projects')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_visits', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'project_task_ref']);
        });

        Schema::dropIfExists('field_service_projects');

        Schema::table('job_stages', function (Blueprint $table) {
            $table->dropColumn('portal_visible');
        });
    }
};
