<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Work\ServiceJob;
use Tests\TestCase;

/**
 * Unit tests for the ServiceJob service_outcome feature.
 *
 * Tests are pure PHP (no DB) — they validate constants, helpers, and logic.
 */
class ServiceJobOutcomeTest extends TestCase
{
    public function test_all_outcome_constants_are_in_outcomes_array(): void
    {
        $this->assertContains(ServiceJob::OUTCOME_COMPLETED_SUCCESSFULLY, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_COMPLETED_WITH_FOLLOWUP, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_COMPLETED_PARTIAL, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_CANCELLED_CUSTOMER, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_CANCELLED_INTERNAL, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_NO_ACCESS, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_NO_SHOW, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_RESCHEDULE_REQUIRED, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_QUOTE_REQUIRED, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED, ServiceJob::OUTCOMES);
        $this->assertContains(ServiceJob::OUTCOME_AGREEMENT_REQUIRED, ServiceJob::OUTCOMES);

        $this->assertCount(11, ServiceJob::OUTCOMES);
    }

    public function test_requires_follow_up_returns_true_for_relevant_outcomes(): void
    {
        $outcomes = [
            ServiceJob::OUTCOME_COMPLETED_WITH_FOLLOWUP,
            ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED,
            ServiceJob::OUTCOME_NO_ACCESS,
            ServiceJob::OUTCOME_NO_SHOW,
            ServiceJob::OUTCOME_RESCHEDULE_REQUIRED,
        ];

        foreach ($outcomes as $outcome) {
            $job = new ServiceJob(['service_outcome' => $outcome]);
            $this->assertTrue(
                $job->requiresFollowUp(),
                "Expected requiresFollowUp() to be true for: {$outcome}"
            );
        }
    }

    public function test_requires_follow_up_returns_false_for_completed_successfully(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_COMPLETED_SUCCESSFULLY]);
        $this->assertFalse($job->requiresFollowUp());
    }

    public function test_requires_quote_is_true_for_quote_outcome(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_QUOTE_REQUIRED]);
        $this->assertTrue($job->requiresQuote());
    }

    public function test_requires_agreement_is_true_for_agreement_outcome(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_AGREEMENT_REQUIRED]);
        $this->assertTrue($job->requiresAgreement());
    }

    public function test_is_successful_completion_is_true_only_for_completed_successfully(): void
    {
        $successful = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_COMPLETED_SUCCESSFULLY]);
        $this->assertTrue($successful->isSuccessfulCompletion());

        $partial = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_COMPLETED_PARTIAL]);
        $this->assertFalse($partial->isSuccessfulCompletion());
    }

    public function test_crm_signal_maps_outcomes_correctly(): void
    {
        $expectations = [
            ServiceJob::OUTCOME_COMPLETED_SUCCESSFULLY  => 'crm_service_completed',
            ServiceJob::OUTCOME_COMPLETED_WITH_FOLLOWUP => 'crm_followup_required',
            ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED   => 'crm_return_visit_required',
            ServiceJob::OUTCOME_QUOTE_REQUIRED          => 'crm_quote_required',
            ServiceJob::OUTCOME_AGREEMENT_REQUIRED      => 'crm_agreement_candidate',
            ServiceJob::OUTCOME_NO_ACCESS               => 'crm_return_visit_required',
            ServiceJob::OUTCOME_NO_SHOW                 => 'crm_return_visit_required',
        ];

        foreach ($expectations as $outcome => $expectedSignal) {
            $job = new ServiceJob(['service_outcome' => $outcome]);
            $this->assertSame(
                $expectedSignal,
                $job->crmSignal(),
                "Expected crmSignal() to return '{$expectedSignal}' for outcome '{$outcome}'"
            );
        }
    }

    public function test_crm_signal_returns_null_for_partial_completion(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_COMPLETED_PARTIAL]);
        $this->assertNull($job->crmSignal());
    }

    public function test_post_service_sales_signals_for_quote_required(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_QUOTE_REQUIRED]);
        $signals = $job->postServiceSalesSignals();

        $this->assertContains('crm_upsell_detected', $signals);
    }

    public function test_post_service_sales_signals_for_agreement_required(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_AGREEMENT_REQUIRED]);
        $signals = $job->postServiceSalesSignals();

        $this->assertContains('crm_agreement_candidate', $signals);
        $this->assertContains('crm_recurring_candidate', $signals);
    }

    public function test_post_service_sales_signals_for_return_visit(): void
    {
        $job = new ServiceJob(['service_outcome' => ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED]);
        $signals = $job->postServiceSalesSignals();

        $this->assertContains('crm_repair_detected', $signals);
    }

    public function test_record_outcome_throws_for_invalid_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $job = new ServiceJob();
        $job->recordOutcome('not_a_real_outcome');
    }
}
