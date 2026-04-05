<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Events\Money\RecommendationApproved;
use App\Events\Money\RecommendationRejected;
use App\Models\Money\FinancialActionRecommendation;
use App\Models\User;
use Illuminate\Support\Facades\Event;

/**
 * FinancialApprovalBridgeService
 *
 * Manages the approval lifecycle for financial action recommendations.
 */
class FinancialApprovalBridgeService
{
    public function approve(int $recommendationId, User $reviewer, ?string $notes = null): FinancialActionRecommendation
    {
        $rec = FinancialActionRecommendation::findOrFail($recommendationId);

        $rec->update([
            'status'       => 'approved',
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        Event::dispatch(new RecommendationApproved($rec, $reviewer));

        return $rec->fresh();
    }

    public function reject(int $recommendationId, User $reviewer, ?string $notes = null): FinancialActionRecommendation
    {
        $rec = FinancialActionRecommendation::findOrFail($recommendationId);

        $rec->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        Event::dispatch(new RecommendationRejected($rec, $reviewer));

        return $rec->fresh();
    }

    public function dismiss(int $recommendationId, User $reviewer, ?string $notes = null): FinancialActionRecommendation
    {
        $rec = FinancialActionRecommendation::findOrFail($recommendationId);

        $rec->update([
            'status'       => 'dismissed',
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        return $rec->fresh();
    }

    public function queue(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return FinancialActionRecommendation::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'pending_review')
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function auditTrail(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return FinancialActionRecommendation::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereNotNull('reviewed_at')
            ->with('reviewer')
            ->orderBy('reviewed_at', 'desc')
            ->get();
    }
}
