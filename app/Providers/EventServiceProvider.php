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
use App\Events\Work\ServicePlanVisitCompleted;
use App\Events\Work\ServicePlanVisitDispatched;
use App\Events\Work\ServicePlanVisitScheduled;
use App\Events\Equipment\EquipmentInstalled;
use App\Events\Equipment\EquipmentRemoved;
use App\Events\Equipment\EquipmentReplaced;
use App\Events\Inspection\InspectionCompleted;
use App\Events\Inspection\InspectionFailed;
use App\Events\Inspection\InspectionFollowupRequired;
use App\Events\Inspection\InspectionScheduled;
use App\Events\Inspection\InspectionStarted;
use App\Events\Premises\HazardDetected;
use App\Events\Premises\HazardResolved;
use App\Events\YokassaWebhookEvent;
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
use App\Listeners\Work\ChecklistRunCompletedListener;
use App\Listeners\Work\JobCompletedListener;
use App\Listeners\Work\JobStageChangedListener;
use App\Listeners\Work\ServicePlanVisitCompletedListener;
use App\Listeners\Work\ServicePlanVisitDispatchedListener;
use App\Listeners\Work\ServicePlanVisitScheduledListener;
use App\Listeners\YokassaWebhookListener;
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
        ],
        InvoicePaid::class => [
            InvoicePaidListener::class,
        ],
        UsersActivityEvent::class => [
            UsersActivityListener::class,
        ],
        // ── Work / Field Service events ───────────────────────────────────
        JobStageChanged::class => [
            JobStageChangedListener::class,
        ],
        JobCompleted::class => [
            JobCompletedListener::class,
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
        // ── Inspection lifecycle signals (canonical Inspection namespace) ──
        InspectionScheduled::class        => [],
        InspectionStarted::class          => [],
        InspectionCompleted::class        => [],
        InspectionFailed::class           => [],
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
