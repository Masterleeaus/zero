<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Work\ChecklistRun;
use App\Models\Work\InspectionInstance;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use App\Models\Work\SiteAsset;
use Tests\TestCase;

/**
 * Unit tests for the WorkCore ↔ FSM linkage audit corrections.
 *
 * Tests are pure PHP (no DB) — they validate model structure, scopes, and helpers.
 */
class WorkCoreFsmLinkageTest extends TestCase
{
    // ── Stage B: ServiceJob scope completeness ────────────────────────────────

    public function test_service_job_has_scope_for_customer(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'scopeForCustomer'));
    }

    public function test_service_job_has_scope_for_premises(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'scopeForPremises'));
    }

    public function test_service_job_has_scope_for_agreement(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'scopeForAgreement'));
    }

    public function test_service_job_has_scope_for_enquiry(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'scopeForEnquiry'));
    }

    public function test_service_job_has_scope_for_deal(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'scopeForDeal'));
    }

    // ── Stage D: Agreement → ServicePlan → ServicePlanVisit chain ─────────────

    public function test_service_agreement_has_service_plan_relation(): void
    {
        $this->assertTrue(method_exists(ServiceAgreement::class, 'servicePlan'));
    }

    public function test_service_agreement_has_service_plans_relation(): void
    {
        $this->assertTrue(method_exists(ServiceAgreement::class, 'servicePlans'));
    }

    public function test_service_agreement_has_premises_relation(): void
    {
        $this->assertTrue(method_exists(ServiceAgreement::class, 'premises'));
    }

    public function test_service_plan_has_agreement_relation(): void
    {
        $this->assertTrue(method_exists(ServicePlan::class, 'agreement'));
    }

    public function test_service_plan_has_visits_relation(): void
    {
        $this->assertTrue(method_exists(ServicePlan::class, 'visits'));
    }

    public function test_service_plan_has_premises_relation(): void
    {
        $this->assertTrue(method_exists(ServicePlan::class, 'premises'));
    }

    public function test_service_plan_visit_has_plan_relation(): void
    {
        $this->assertTrue(method_exists(ServicePlanVisit::class, 'plan'));
    }

    public function test_service_plan_visit_has_service_job_relation(): void
    {
        $this->assertTrue(method_exists(ServicePlanVisit::class, 'serviceJob'));
    }

    public function test_service_plan_default_status_is_active(): void
    {
        $plan = new ServicePlan();
        $this->assertSame('active', $plan->status);
    }

    public function test_service_plan_visit_default_status_is_pending(): void
    {
        $visit = new ServicePlanVisit();
        $this->assertSame('pending', $visit->status);
    }

    // ── Stage I: SiteAsset model ───────────────────────────────────────────────

    public function test_site_asset_has_premises_relation(): void
    {
        $this->assertTrue(method_exists(SiteAsset::class, 'premises'));
    }

    public function test_site_asset_has_customer_relation(): void
    {
        $this->assertTrue(method_exists(SiteAsset::class, 'customer'));
    }

    public function test_site_asset_has_agreement_relation(): void
    {
        $this->assertTrue(method_exists(SiteAsset::class, 'agreement'));
    }

    public function test_site_asset_default_status_is_active(): void
    {
        $asset = new SiteAsset();
        $this->assertSame('active', $asset->status);
    }

    public function test_site_asset_is_active_helper(): void
    {
        $asset = new SiteAsset(['status' => 'active']);
        $this->assertTrue($asset->isActive());

        $decommissioned = new SiteAsset(['status' => 'decommissioned']);
        $this->assertFalse($decommissioned->isActive());
    }

    public function test_site_asset_is_service_due_when_past_date(): void
    {
        $asset = new SiteAsset(['next_service_due' => '2000-01-01']);
        $this->assertTrue($asset->isServiceDue());
    }

    public function test_site_asset_is_not_service_due_when_future_date(): void
    {
        $asset = new SiteAsset(['next_service_due' => '2999-01-01']);
        $this->assertFalse($asset->isServiceDue());
    }

    // ── Stage J: InspectionInstance model ────────────────────────────────────

    public function test_inspection_instance_has_service_job_relation(): void
    {
        $this->assertTrue(method_exists(InspectionInstance::class, 'serviceJob'));
    }

    public function test_inspection_instance_has_premises_relation(): void
    {
        $this->assertTrue(method_exists(InspectionInstance::class, 'premises'));
    }

    public function test_inspection_instance_has_checklist_runs_relation(): void
    {
        $this->assertTrue(method_exists(InspectionInstance::class, 'checklistRuns'));
    }

    public function test_inspection_instance_has_site_asset_relation(): void
    {
        $this->assertTrue(method_exists(InspectionInstance::class, 'siteAsset'));
    }

    public function test_inspection_instance_default_status_is_pending(): void
    {
        $inspection = new InspectionInstance();
        $this->assertSame('pending', $inspection->status);
    }

    public function test_inspection_is_passed_when_completed(): void
    {
        $inspection = new InspectionInstance(['status' => 'completed']);
        $this->assertTrue($inspection->isPassed());
        $this->assertFalse($inspection->isFailed());
    }

    public function test_inspection_is_failed_when_failed(): void
    {
        $inspection = new InspectionInstance(['status' => 'failed']);
        $this->assertTrue($inspection->isFailed());
        $this->assertFalse($inspection->isPassed());
    }

    // ── Stage J: ChecklistRun model ───────────────────────────────────────────

    public function test_checklist_run_has_service_job_relation(): void
    {
        $this->assertTrue(method_exists(ChecklistRun::class, 'serviceJob'));
    }

    public function test_checklist_run_has_inspection_instance_relation(): void
    {
        $this->assertTrue(method_exists(ChecklistRun::class, 'inspectionInstance'));
    }

    public function test_checklist_run_has_premises_relation(): void
    {
        $this->assertTrue(method_exists(ChecklistRun::class, 'premises'));
    }

    public function test_checklist_run_completion_percentage(): void
    {
        $run = new ChecklistRun([
            'items_total'     => 10,
            'items_completed' => 7,
        ]);

        $this->assertSame(70, $run->completionPercentage());
    }

    public function test_checklist_run_completion_percentage_with_zero_total(): void
    {
        $run = new ChecklistRun(['items_total' => 0, 'items_completed' => 0]);
        $this->assertSame(0, $run->completionPercentage());
    }

    public function test_checklist_run_has_failed_when_items_failed_gt_zero(): void
    {
        $run = new ChecklistRun(['items_failed' => 1]);
        $this->assertTrue($run->hasFailed());
    }

    // ── Stage B/J: ServiceJob → InspectionInstance + ChecklistRun relations ──

    public function test_service_job_has_inspections_relation(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'inspections'));
    }

    public function test_service_job_has_checklist_runs_relation(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'checklistRuns'));
    }

    public function test_service_job_has_plan_visit_relation(): void
    {
        $this->assertTrue(method_exists(ServiceJob::class, 'planVisit'));
    }

    // ── Stage K: Event class existence ────────────────────────────────────────

    public function test_equipment_installed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Equipment\EquipmentInstalled::class));
    }

    public function test_equipment_removed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Equipment\EquipmentRemoved::class));
    }

    public function test_equipment_replaced_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Equipment\EquipmentReplaced::class));
    }

    public function test_inspection_completed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Work\InspectionCompleted::class));
    }

    public function test_inspection_failed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Work\InspectionFailed::class));
    }

    public function test_job_cancelled_event_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\Work\JobCancelled::class));
    }
}
