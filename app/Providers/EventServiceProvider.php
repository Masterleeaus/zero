<?php

namespace App\Providers;

use App\Events\BankTransferEvent;
use App\Events\FreePaymentEvent;
use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Events\IyzicoLifetimeEvent;
use App\Events\IyzicoWebhookEvent;
use App\Events\PaypalLifetimeEvent;
use App\Events\PaypalWebhookEvent;
use App\Events\PaystackLifetimeEvent;
use App\Events\PaystackWebhookEvent;
use App\Events\QuoteAccepted;
use App\Events\StripeLifetimeEvent;
use App\Events\StripeWebhookEvent;
use App\Events\TwoCheckoutWebhookEvent;
use App\Events\UsersActivityEvent;
use App\Events\Work\ActivityCompleted;
use App\Events\Work\ActivityCreated;
use App\Events\Work\ActivityDismissed;
use App\Events\Work\AgreementServiceConsumed;
use App\Events\Work\ChecklistRunCompleted;
use App\Events\Work\InspectionCompleted as WorkInspectionCompleted;
use App\Events\Work\InspectionFailed as WorkInspectionFailed;
use App\Events\Work\JobAssigned;
use App\Events\Work\JobCancelled;
use App\Events\Work\JobCompleted;
use App\Events\Work\JobCompletedBillable;
use App\Events\Work\JobMarkedBillable;
use App\Events\Work\JobReadyForInvoice;
use App\Events\Work\JobStageChanged;
use App\Events\Work\JobStarted;
use App\Events\Work\ServiceInvoiceGenerated;
use App\Events\Work\ServiceJobRescheduled;
use App\Events\Work\ServiceJobScheduled;
use App\Events\Work\ServiceJobUnscheduled;
use App\Events\Work\ServicePlanVisitCompleted;
use App\Events\Work\ServicePlanVisitDispatched;
use App\Events\Work\ServicePlanVisitRescheduled;
use App\Events\Work\ServicePlanVisitScheduled;
// Module 23 — fieldservice_kanban_info signals
use App\Events\Work\JobKanbanStateChanged;
use App\Events\Work\JobBlockerAdded;
use App\Events\Work\JobBlockerCleared;
use App\Events\Equipment\EquipmentInstalled;
use App\Events\Equipment\EquipmentRemoved;
use App\Events\Equipment\EquipmentReplaced;
use App\Events\Equipment\EquipmentWarrantyActivated;
use App\Events\Equipment\EquipmentWarrantyExpired;
use App\Events\Equipment\EquipmentWarrantyExpiringSoon;
use App\Events\Equipment\WarrantyClaimApproved;
use App\Events\Equipment\WarrantyClaimCompleted;
use App\Events\Equipment\WarrantyClaimCreated;
use App\Events\Equipment\WarrantyClaimRejected;
use App\Events\Inspection\InspectionCompleted;
use App\Events\Inspection\InspectionFailed;
use App\Events\Inspection\InspectionFollowupRequired;
use App\Events\Inspection\InspectionRescheduled;
use App\Events\Inspection\InspectionScheduled;
use App\Events\Inspection\InspectionStarted;
use App\Events\Premises\HazardDetected;
use App\Events\Premises\HazardResolved;
use App\Events\Route\RouteAssigned;
use App\Events\Route\RouteCapacityExceeded;
use App\Events\Route\RouteConflictDetected;
use App\Events\Route\RouteCreated;
use App\Events\Route\RouteStopAdded;
use App\Events\Route\RouteStopCompleted;
use App\Events\Route\RouteStopFailed;
use App\Events\Route\RouteStopReordered;
use App\Events\Route\RouteUpdated;
use App\Events\Route\TechnicianAvailabilityCreated;
use App\Events\Route\TechnicianAvailabilityUpdated;
use App\Events\Crm\CrmWarrantyClaimOpened;
use App\Events\Crm\CrmWarrantyClaimRejected;
use App\Events\Crm\CrmWarrantyExpiring;
use App\Events\Crm\CrmWarrantyReplacementOpportunity;
use App\Events\YokassaWebhookEvent;
// ── Finance / Accounting events (Finance Pass 1) ──────────────────────────────
use App\Events\Money\ExpenseApproved;
use App\Events\Money\PaymentRecorded;
use App\Listeners\Money\PostExpenseApprovedToLedger;
use App\Listeners\Money\PostInvoiceIssuedToLedger;
use App\Listeners\Money\PostPaymentRecordedToLedger;
use App\Listeners\BankTransferListener;
use App\Listeners\FreePaymentListener;
use App\Listeners\InvoiceIssuedListener;
use App\Listeners\InvoicePaidListener;
use App\Listeners\IyzicoLifetimeListener;
use App\Listeners\IyzicoWebhookListener;
use App\Listeners\PaypalLifetimeListener;
use App\Listeners\PaypalWebhookListener;
use App\Listeners\PaystackLifetimeListener;
use App\Listeners\PaystackWebhookListener;
use App\Listeners\Premises\HazardDetectedListener;
use App\Listeners\Premises\HazardResolvedListener;
use App\Listeners\QuoteAcceptedListener;
use App\Listeners\StripeLifetimeListener;
use App\Listeners\StripeWebhookListener;
use App\Listeners\TwoCheckoutWebhookListener;
use App\Listeners\UsersActivityListener;
use App\Listeners\Equipment\EquipmentWarrantyExpiredListener;
use App\Listeners\Equipment\EquipmentWarrantyExpiringSoonListener;
use App\Listeners\Equipment\WarrantyClaimCreatedListener;
use App\Listeners\Equipment\WarrantyClaimRejectedListener;
use App\Listeners\Work\ChecklistRunCompletedListener;
use App\Listeners\Work\JobCompletedListener;
use App\Listeners\Work\JobKanbanStateChangedListener;
use App\Listeners\Work\JobStageChangedListener;
use App\Listeners\Work\ServiceJobRescheduledListener;
use App\Listeners\Work\ServiceJobScheduledListener;
use App\Listeners\Work\ServicePlanVisitCompletedListener;
use App\Listeners\Work\ServicePlanVisitDispatchedListener;
use App\Listeners\Work\ServicePlanVisitScheduledListener;
use App\Listeners\Route\RouteAssignedListener;
use App\Listeners\Route\RouteCapacityExceededListener;
use App\Listeners\Route\RouteStopCompletedListener;
use App\Listeners\YokassaWebhookListener;
// ── MODULE 03 TrustWorkLedger ──────────────────────────────────────────────
use App\Events\Trust\ChainSealed;
use App\Events\Trust\ChainTamperingDetected;
use App\Events\Trust\LedgerEntryRecorded;
use App\Listeners\Trust\RecordInspectionCompletedOnLedger;
use App\Listeners\Trust\RecordInspectionFailedOnLedger;
use App\Listeners\Trust\RecordJobCompletionOnLedger;
// ── Repair domain events (Modules 9 + 10) ────────────────────────────────────
use App\Events\Repair\RepairOrderCreated;
use App\Events\Repair\RepairOrderCompleted;
use App\Events\Repair\RepairOrderCancelled;
use App\Events\Repair\RepairWarrantyDetected;
use App\Events\Repair\RepairClaimRequired;
use App\Events\Repair\RepairClaimLinked;
use App\Events\Repair\RepairWarrantyApplied;
use App\Events\Repair\RepairDiagnosisRecorded;
use App\Events\Repair\RepairSpecialistRequired;
use App\Events\Repair\RepairQuoteRequired;
use App\Events\Repair\RepairPartsReserved;
use App\Events\Repair\RepairPartsConsumed;
use App\Events\Repair\RepairPartsPending;
use App\Events\Repair\RepairTemplateApplied;
use App\Events\Repair\RepairTemplateGeneratedChecklist;
use App\Events\Repair\RepairTemplateGeneratedParts;
use App\Events\Repair\PremisesRepairCreated;
use App\Events\Repair\PremisesRepairEscalated;
use App\Events\Repair\PremisesRepairClosed;
use App\Events\Repair\ServiceRepairRequired;
use App\Events\Repair\RepairCreatedFromService;
use App\Events\Repair\RepairFollowupScheduled;
use App\Events\Repair\CrmRepairDetected;
use App\Events\Repair\CrmReplacementCandidate;
use App\Events\Repair\CrmServiceUpgradeCandidate;
use App\Events\Repair\CrmAgreementCandidate;
use App\Events\Repair\CrmRecurringCandidate;
use App\Events\Repair\RepairInvoiceGenerated;
use App\Events\Repair\RepairClaimOffsetApplied;
use App\Events\Repair\RepairScheduled;
use App\Events\Repair\RepairRescheduled;
use App\Events\Repair\RepairDispatchAssigned;
// ── Module 21 — fieldservice_portal events ────────────────────────────────────
use App\Events\Work\PortalBookingRequested;
use App\Events\Work\PortalVisitConfirmed;
use App\Events\Work\PortalQuoteApproved;
use App\Events\Work\PortalPaymentSubmitted;
use App\Events\Work\PortalFeedbackSubmitted;
use App\Events\Work\PortalMessageCreated;
// ── Module 22 — fieldservice_project events ───────────────────────────────────
use App\Events\Work\FieldServiceProjectCreated;
use App\Events\Work\FieldServiceProjectUpdated;
use App\Events\Work\FieldServiceProjectJobLinked;
use App\Events\Work\FieldServiceProjectVisitLinked;
use App\Events\Work\FieldServiceProjectCompleted;
use App\Listeners\Repair\RepairOrderCreatedListener;
use App\Listeners\Repair\RepairOrderCompletedListener;
use App\Listeners\Repair\RepairWarrantyDetectedListener;
use App\Listeners\Repair\RepairDiagnosisRecordedListener;
use App\Listeners\Repair\RepairTemplateAppliedListener;
use App\Listeners\Work\FieldServiceProjectCreatedListener;
use App\Listeners\Work\FieldServiceProjectCompletedListener;
// ── fieldservice_sale + fieldservice_sale_agreement events ───────────────────
use App\Events\Work\JobDispatched;
use App\Events\Work\JobDispatchFailed;
use App\Events\Work\JobReDispatched;
use App\Listeners\Work\NotifyTechnicianOfAssignment;
use App\Listeners\Work\RecordDispatchAuditTrail;
// ── MODULE_02 CapabilityRegistry ────────────────────────────────────────────
use App\Events\Team\SkillAssigned;
use App\Events\Team\CertificationExpired;
use App\Events\Team\CertificationRevoked;
use App\Events\Team\CapabilityGapDetected;
use App\Listeners\Team\NotifyOnCertificationExpiry;
use App\Listeners\Team\RecordCapabilityAuditTrail;
// ── MODULE_07 TitanPredict ───────────────────────────────────────────────────
use App\Events\Predict\PredictionGenerated;
use App\Events\Predict\HighConfidencePrediction;
use App\Events\Predict\PredictionTriggered;
use App\Events\Predict\PredictionFeedbackRecorded;
use App\Listeners\Predict\NotifyOnHighConfidencePrediction;
use App\Listeners\Predict\UpdateAssetPredictionOnServiceEvent;
use App\Listeners\Predict\UpdateSLAPredictionOnJobCompletion;
// ── MODULE 09 — ExecutionFinanceLayer ────────────────────────────────────────
use App\Events\Finance\JobCostRecorded;
use App\Events\Finance\JobFinancialSummaryUpdated;
use App\Events\Finance\UnprofitableJobDetected;
use App\Events\Finance\JobInvoiced as FinanceJobInvoiced;
use App\Listeners\Finance\RecalculateFinancialSummaryOnCostChange;
use App\Listeners\Finance\NotifyOnUnprofitableJob;
use App\Listeners\Finance\RecordRevenueOnJobBilled;
// ── MODULE_10 TitanMesh ──────────────────────────────────────────────────────
use App\Events\Mesh\MeshNodeHandshaked;
use App\Events\Mesh\MeshDispatchRequested;
use App\Events\Mesh\MeshDispatchAccepted;
use App\Events\Mesh\MeshDispatchCompleted;
use App\Events\Mesh\MeshTrustChanged;
use App\Events\Mesh\MeshSettlementDue;
use App\Listeners\Mesh\RecordMeshOperationOnTrustLedger;
use App\Listeners\Mesh\RecordMeshEventOnTimeGraph;
use App\Listeners\Mesh\NotifyOnMeshDispatchAccepted;
use App\Events\Work\FieldServiceSaleCreated;
use App\Events\Work\FieldServiceSaleApproved;
use App\Events\Work\FieldServiceSaleConvertedToJob;
use App\Events\Work\FieldServiceSaleConvertedToPlan;
use App\Events\Work\FieldServiceAgreementSaleCreated;
use App\Events\Work\FieldServiceAgreementSaleActivated;
use App\Events\Work\FieldServiceAgreementSaleExtended;
use App\Events\Work\SaleServiceCoverageApplied;
// ── FSM Modules — fieldservice_sale_agreement lifecycle events ───────────────
use App\Events\Work\FieldServiceAgreementCreated;
use App\Events\Work\FieldServiceAgreementUpdated;
use App\Events\Work\FieldServiceAgreementActivated;
use App\Events\Work\FieldServiceAgreementExpired;
use App\Events\Work\FieldServiceAgreementRenewed;
use App\Events\Work\FieldServiceAgreementCancelled;
// ── FSM Drift Repair — previously unregistered Work events ──────────────────
use App\Events\Work\ActivityFollowUpScheduled;
use App\Events\Work\AgreementEquipmentCoverageCreated;
use App\Events\Work\AgreementEquipmentCoverageExtended;
use App\Events\Work\DispatchETAChanged;
use App\Events\Work\DispatchJobLate;
use App\Events\Work\DispatchReadinessChanged;
use App\Events\Work\DispatchStockBlocked;
use App\Events\Work\DispatchVehicleBlocked;
use App\Events\Work\RecurringEquipmentServiceCreated;
use App\Events\Work\RecurringPlanGenerated;
use App\Events\Work\RecurringPlanUpdated;
use App\Events\Work\RecurringSaleCreated;
use App\Events\Work\RecurringVisitMaterialized;
use App\Events\Work\SaleRecurringAgreementCreated;
use App\Events\Work\SaleRecurringAgreementUpdated;
use App\Events\Work\SaleRecurringCoverageApplied;
use App\Events\Work\SaleRecurringPlanGenerated;
use App\Events\Work\SaleRecurringVisitMaterialized;
use App\Events\Work\SaleRecurringVisitProjected;
use App\Events\Work\TimesheetApproved;
use App\Events\Work\TimesheetRejected;
use App\Events\Work\TimesheetSubmitted;
use App\Events\Work\VehicleAssignedToJob;
use App\Events\Work\VehicleEquipmentMissing;
use App\Events\Work\VehicleLocationUpdated;
use App\Events\Work\VehicleRouteReady;
use App\Events\Work\VehicleStockConsumed;
use App\Events\Work\VehicleStockReserved;
use App\Listeners\Work\FieldServiceSaleApprovedListener;
use App\Listeners\Work\FieldServiceAgreementSaleActivatedListener;
// ── MODULE 04 TitanContracts — agreement entitlement signals ──────────────
use App\Events\Work\ContractEntitlementExhausted;
use App\Events\Work\ContractExpired;
use App\Events\Work\ContractHealthDegraded;
use App\Events\Work\ContractRenewed;
use App\Events\Work\ContractSLABreached;
use App\Listeners\Work\CheckSLAOnJobStatusChange;
use App\Listeners\Work\NotifyOnContractExpiry;
use App\Listeners\Work\UpdateContractHealthOnJobCompletion;
use App\Listeners\Crm\LinkAgreementToDeal;
// ── MODULE 05 — TitanEdgeSync events ─────────────────────────────────────────
use App\Events\Sync\EdgeBatchSynced;
use App\Events\Sync\EdgeConflictDetected;
use App\Events\Sync\EdgeConflictResolved;
use App\Events\Sync\EdgeSyncFailed;
use App\Listeners\Sync\RecordSyncEventOnTrustLedger;
// ── MODULE_06 ExecutionTimeGraph events ──────────────────────────────────────
use App\Events\TimeGraph\ExecutionGraphOpened;
use App\Events\TimeGraph\ExecutionGraphCompleted;
use App\Events\TimeGraph\ExecutionCheckpointCreated;
use App\Events\TimeGraph\ExecutionAnomalyDetected;
// ── MODULE_08 DocsExecutionBridge ────────────────────────────────────────────
use App\Events\Docs\DocumentsInjectedForJob;
use App\Events\Docs\MandatoryDocumentAcknowledged;
use App\Events\Docs\DocumentVersionPublished;
use App\Events\Docs\DocumentReviewDue;
use App\Events\Work\JobCreated;
use App\Listeners\Docs\InjectDocumentsOnJobCreated;
use App\Listeners\Docs\InjectDocumentsOnInspectionScheduled;
use App\Listeners\Docs\BlockJobCompletionIfMandatoryUnacknowledged;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PaypalWebhookEvent::class => [
            PaypalWebhookListener::class,
        ],
        StripeWebhookEvent::class => [
            StripeWebhookListener::class,
        ],
        YokassaWebhookEvent::class => [
            YokassaWebhookListener::class,
        ],
        TwoCheckoutWebhookEvent::class => [
            TwoCheckoutWebhookListener::class,
        ],
        IyzicoWebhookEvent::class => [
            IyzicoWebhookListener::class,
        ],
        PaystackWebhookEvent::class => [
            PaystackWebhookListener::class,
        ],
        BankTransferEvent::class => [
            BankTransferListener::class,
        ],
        FreePaymentEvent::class => [
            FreePaymentListener::class,
        ],
        StripeLifetimeEvent::class => [
            StripeLifetimeListener::class,
        ],
        PaypalLifetimeEvent::class => [
            PaypalLifetimeListener::class,
        ],
        IyzicoLifetimeEvent::class => [
            IyzicoLifetimeListener::class,
        ],
        PaystackLifetimeEvent::class => [
            PaystackLifetimeListener::class,
        ],
        QuoteAccepted::class => [
            QuoteAcceptedListener::class,
        ],
        InvoiceIssued::class => [
            InvoiceIssuedListener::class,
            PostInvoiceIssuedToLedger::class,
        ],
        InvoicePaid::class => [
            InvoicePaidListener::class,
        ],
        // ── Finance Pass 1 — auto-posting accounting listeners ────────────────
        PaymentRecorded::class => [
            PostPaymentRecordedToLedger::class,
        ],
        ExpenseApproved::class => [
            PostExpenseApprovedToLedger::class,
        ],
        UsersActivityEvent::class => [
            UsersActivityListener::class,
        ],
        // ── Work / Field Service events ───────────────────────────────────
        JobStageChanged::class => [
            JobStageChangedListener::class,
            BlockJobCompletionIfMandatoryUnacknowledged::class,
        ],
        JobCompleted::class => [
            JobCompletedListener::class,
            RecordJobCompletionOnLedger::class,
        ],
        // The following Work events are defined for downstream automation.
        // Listeners can be added here as automation rules are wired up.
        JobStarted::class            => [],
        JobCancelled::class          => [],
        JobAssigned::class           => [],
        JobMarkedBillable::class     => [],
        JobReadyForInvoice::class    => [],
        JobCompletedBillable::class  => [],
        AgreementServiceConsumed::class => [],
        ServiceInvoiceGenerated::class  => [],
        // Module 4 — fieldservice_activity lifecycle signals
        ActivityCreated::class   => [],
        ActivityCompleted::class => [],
        ActivityDismissed::class => [],
        // Work-namespace inspection signals (from fieldservice layer)
        WorkInspectionCompleted::class => [],
        WorkInspectionFailed::class    => [],
        // ── Module 9 (fieldservice_calendar) — calendar lifecycle signals ──
        ServiceJobScheduled::class => [
            ServiceJobScheduledListener::class,
        ],
        ServiceJobRescheduled::class => [
            ServiceJobRescheduledListener::class,
        ],
        ServiceJobUnscheduled::class => [],
        ServicePlanVisitRescheduled::class => [],
        InspectionRescheduled::class       => [],
        // ── Inspection lifecycle signals (canonical Inspection namespace) ──
        InspectionScheduled::class        => [
            InjectDocumentsOnInspectionScheduled::class,
        ],
        InspectionStarted::class          => [],
        InspectionCompleted::class        => [
            RecordInspectionCompletedOnLedger::class,
        ],
        InspectionFailed::class           => [
            RecordInspectionFailedOnLedger::class,
        ],
        InspectionFollowupRequired::class => [],
        // ── Equipment lifecycle signals ────────────────────────────────────
        EquipmentInstalled::class => [],
        EquipmentRemoved::class   => [],
        EquipmentReplaced::class  => [],
        // ── ServicePlanVisit lifecycle signals ────────────────────────────
        ServicePlanVisitScheduled::class  => [
            ServicePlanVisitScheduledListener::class,
        ],
        ServicePlanVisitDispatched::class => [
            ServicePlanVisitDispatchedListener::class,
        ],
        ServicePlanVisitCompleted::class  => [
            ServicePlanVisitCompletedListener::class,
        ],
        // ── Checklist lifecycle signals ───────────────────────────────────
        ChecklistRunCompleted::class => [
            ChecklistRunCompletedListener::class,
        ],
        // ── Hazard lifecycle signals ──────────────────────────────────────
        HazardDetected::class => [
            HazardDetectedListener::class,
        ],
        HazardResolved::class => [
            HazardResolvedListener::class,
        ],
        // ── Equipment warranty lifecycle signals (Module 8) ───────────────
        EquipmentWarrantyActivated::class => [],
        EquipmentWarrantyExpiringSoon::class => [
            EquipmentWarrantyExpiringSoonListener::class,
        ],
        EquipmentWarrantyExpired::class => [
            EquipmentWarrantyExpiredListener::class,
        ],
        WarrantyClaimCreated::class => [
            WarrantyClaimCreatedListener::class,
        ],
        WarrantyClaimApproved::class  => [],
        WarrantyClaimRejected::class  => [
            WarrantyClaimRejectedListener::class,
        ],
        WarrantyClaimCompleted::class => [],
        // ── CRM warranty signals (Module 8) ──────────────────────────────
        CrmWarrantyExpiring::class               => [],
        CrmWarrantyClaimOpened::class            => [],
        CrmWarrantyClaimRejected::class          => [],
        CrmWarrantyReplacementOpportunity::class => [],
        // ── Module 11 (fieldservice_route) — route lifecycle signals ──────
        RouteCreated::class => [],
        RouteUpdated::class => [],
        RouteAssigned::class => [
            RouteAssignedListener::class,
        ],
        RouteCapacityExceeded::class => [
            RouteCapacityExceededListener::class,
        ],
        RouteConflictDetected::class => [],
        RouteStopAdded::class        => [],
        RouteStopCompleted::class    => [
            RouteStopCompletedListener::class,
        ],
        RouteStopFailed::class     => [],
        RouteStopReordered::class  => [],
        TechnicianAvailabilityCreated::class => [],
        TechnicianAvailabilityUpdated::class => [],
        // ── Repair domain signals (Modules 9 + 10) ────────────────────────
        RepairOrderCreated::class => [
            RepairOrderCreatedListener::class,
        ],
        RepairOrderCompleted::class => [
            RepairOrderCompletedListener::class,
        ],
        RepairOrderCancelled::class         => [],
        RepairWarrantyDetected::class       => [
            RepairWarrantyDetectedListener::class,
        ],
        RepairClaimRequired::class          => [],
        RepairClaimLinked::class            => [],
        RepairWarrantyApplied::class        => [],
        RepairDiagnosisRecorded::class      => [
            RepairDiagnosisRecordedListener::class,
        ],
        RepairSpecialistRequired::class     => [],
        RepairQuoteRequired::class          => [],
        RepairPartsReserved::class          => [],
        RepairPartsConsumed::class          => [],
        RepairPartsPending::class           => [],
        RepairTemplateApplied::class        => [
            RepairTemplateAppliedListener::class,
        ],
        RepairTemplateGeneratedChecklist::class => [],
        RepairTemplateGeneratedParts::class     => [],
        PremisesRepairCreated::class        => [],
        PremisesRepairEscalated::class      => [],
        PremisesRepairClosed::class         => [],
        ServiceRepairRequired::class        => [],
        RepairCreatedFromService::class     => [],
        RepairFollowupScheduled::class      => [],
        CrmRepairDetected::class            => [],
        CrmReplacementCandidate::class      => [],
        CrmServiceUpgradeCandidate::class   => [],
        CrmAgreementCandidate::class        => [],
        CrmRecurringCandidate::class        => [],
        RepairInvoiceGenerated::class       => [],
        RepairClaimOffsetApplied::class     => [],
        RepairScheduled::class              => [],
        RepairRescheduled::class            => [],
        RepairDispatchAssigned::class       => [],
        // ── Module 21 — fieldservice_portal signals ───────────────────────────
        PortalBookingRequested::class  => [],
        PortalVisitConfirmed::class    => [],
        PortalQuoteApproved::class     => [],
        PortalPaymentSubmitted::class  => [],
        PortalFeedbackSubmitted::class => [],
        PortalMessageCreated::class    => [],
        // ── Module 22 — fieldservice_project signals ──────────────────────────
        FieldServiceProjectCreated::class => [
            FieldServiceProjectCreatedListener::class,
        ],
        FieldServiceProjectUpdated::class     => [],
        FieldServiceProjectJobLinked::class   => [],
        FieldServiceProjectVisitLinked::class => [],
        FieldServiceProjectCompleted::class   => [
            FieldServiceProjectCompletedListener::class,
        ],
        // ── Module 23 (fieldservice_kanban_info) — kanban intelligence signals ──
        JobKanbanStateChanged::class => [
            JobKanbanStateChangedListener::class,
        ],
        JobBlockerAdded::class   => [],
        JobBlockerCleared::class => [],
        // ── fieldservice_sale + fieldservice_sale_agreement (Modules 3 + 3a) ──
        FieldServiceSaleCreated::class            => [],
        FieldServiceSaleApproved::class           => [
            FieldServiceSaleApprovedListener::class,
        ],
        FieldServiceSaleConvertedToJob::class     => [],
        FieldServiceSaleConvertedToPlan::class    => [],
        FieldServiceAgreementSaleCreated::class   => [],
        FieldServiceAgreementSaleActivated::class => [
            FieldServiceAgreementSaleActivatedListener::class,
        ],
        FieldServiceAgreementSaleExtended::class  => [],
        SaleServiceCoverageApplied::class         => [],
        // ── fieldservice_sale_agreement lifecycle events ──────────────────────
        FieldServiceAgreementCreated::class   => [],
        FieldServiceAgreementUpdated::class   => [],
        FieldServiceAgreementActivated::class => [],
        FieldServiceAgreementExpired::class   => [],
        FieldServiceAgreementRenewed::class   => [],
        FieldServiceAgreementCancelled::class => [],
        // ── FSM Drift Repair — Dispatch readiness signals ─────────────────────
        DispatchETAChanged::class        => [],
        DispatchJobLate::class           => [],
        DispatchReadinessChanged::class  => [],
        DispatchStockBlocked::class      => [],
        DispatchVehicleBlocked::class    => [],
        // ── FSM Drift Repair — Activity follow-up ─────────────────────────────
        ActivityFollowUpScheduled::class => [],
        // ── FSM Drift Repair — Agreement equipment coverage ───────────────────
        AgreementEquipmentCoverageCreated::class  => [],
        AgreementEquipmentCoverageExtended::class => [],
        // ── FSM Drift Repair — Recurring plan lifecycle ───────────────────────
        RecurringEquipmentServiceCreated::class => [],
        RecurringPlanGenerated::class           => [],
        RecurringPlanUpdated::class             => [],
        RecurringSaleCreated::class             => [],
        RecurringVisitMaterialized::class       => [],
        // ── FSM Drift Repair — Sale recurring agreement lifecycle ─────────────
        SaleRecurringAgreementCreated::class    => [],
        SaleRecurringAgreementUpdated::class    => [],
        SaleRecurringCoverageApplied::class     => [],
        SaleRecurringPlanGenerated::class       => [],
        SaleRecurringVisitMaterialized::class   => [],
        SaleRecurringVisitProjected::class      => [],
        // ── FSM Drift Repair — HRM / Timesheet signals ────────────────────────
        TimesheetSubmitted::class => [],
        TimesheetApproved::class  => [],
        TimesheetRejected::class  => [],
        // ── FSM Drift Repair — Vehicle operational signals ────────────────────
        VehicleAssignedToJob::class   => [],
        VehicleEquipmentMissing::class => [],
        VehicleLocationUpdated::class  => [],
        VehicleRouteReady::class       => [],
        VehicleStockReserved::class    => [],
        VehicleStockConsumed::class    => [],
        // ── MODULE_01 TitanDispatch — dispatch lifecycle signals ──────────
        JobDispatched::class => [
            RecordDispatchAuditTrail::class,
            NotifyTechnicianOfAssignment::class,
        ],
        JobDispatchFailed::class  => [],
        JobReDispatched::class    => [],
        // ── MODULE_02 CapabilityRegistry — skill and certification signals ─────
        SkillAssigned::class => [
            RecordCapabilityAuditTrail::class,
        ],
        CertificationExpired::class => [
            NotifyOnCertificationExpiry::class,
            RecordCapabilityAuditTrail::class,
        ],
        CertificationRevoked::class => [
            RecordCapabilityAuditTrail::class,
        ],
        CapabilityGapDetected::class => [
            RecordCapabilityAuditTrail::class,
        ],
        // ── MODULE 03 TrustWorkLedger — trust signals ──────────────────────
        LedgerEntryRecorded::class    => [],
        ChainTamperingDetected::class => [],
        ChainSealed::class            => [],
        // ── MODULE 04 TitanContracts — entitlement + SLA + renewal signals ──
        ContractEntitlementExhausted::class => [],
        ContractExpired::class => [
            NotifyOnContractExpiry::class,
        ],
        ContractHealthDegraded::class => [
            UpdateContractHealthOnJobCompletion::class,
        ],
        ContractRenewed::class        => [],
        ContractSLABreached::class    => [
            CheckSLAOnJobStatusChange::class,
        ],
        // ── MODULE 05 — TitanEdgeSync lifecycle signals ───────────────────
        EdgeBatchSynced::class      => [
            RecordSyncEventOnTrustLedger::class,
        ],
        EdgeConflictDetected::class => [],
        EdgeConflictResolved::class => [],
        EdgeSyncFailed::class       => [],
        // ── MODULE_06 ExecutionTimeGraph — temporal lifecycle signals ─────────
        ExecutionGraphOpened::class       => [],
        ExecutionGraphCompleted::class    => [],
        ExecutionCheckpointCreated::class => [],
        ExecutionAnomalyDetected::class   => [],
        // ── MODULE_07 TitanPredict — predictive lifecycle signals ──────────────
        PredictionGenerated::class => [],
        HighConfidencePrediction::class => [
            NotifyOnHighConfidencePrediction::class,
        ],
        PredictionTriggered::class       => [],
        PredictionFeedbackRecorded::class => [],
        // ── MODULE_08 DocsExecutionBridge — document injection signals ────────
        JobCreated::class => [
            InjectDocumentsOnJobCreated::class,
        ],
        DocumentsInjectedForJob::class    => [],
        MandatoryDocumentAcknowledged::class => [],
        DocumentVersionPublished::class   => [],
        DocumentReviewDue::class          => [],
        // ── MODULE 09 — ExecutionFinanceLayer ──────────────────────────────────────
        JobCostRecorded::class => [
            RecalculateFinancialSummaryOnCostChange::class,
        ],
        JobFinancialSummaryUpdated::class => [],
        UnprofitableJobDetected::class => [
            NotifyOnUnprofitableJob::class,
        ],
        FinanceJobInvoiced::class => [
            RecordRevenueOnJobBilled::class,
        ],
        // ── MODULE_10 TitanMesh — federated capability exchange signals ───────
        MeshNodeHandshaked::class => [
            RecordMeshOperationOnTrustLedger::class,
            RecordMeshEventOnTimeGraph::class,
        ],
        MeshDispatchRequested::class => [
            RecordMeshEventOnTimeGraph::class,
        ],
        MeshDispatchAccepted::class => [
            NotifyOnMeshDispatchAccepted::class,
        ],
        MeshDispatchCompleted::class => [
            RecordMeshOperationOnTrustLedger::class,
            RecordMeshEventOnTimeGraph::class,
        ],
        MeshTrustChanged::class => [
            RecordMeshEventOnTimeGraph::class,
        ],
        MeshSettlementDue::class => [],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
