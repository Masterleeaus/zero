<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Start a 14-day trial for a newly created store.
     */
    public function startTrial(Store $store, Plan $plan)
    {
        return $store->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => Carbon::now()->addDays(14),
        ]);
    }

    /**
     * Check if a store has hit the limit for a specific feature/resource based on their active plan.
     */
    public function checkResourceLimit(Store $store, string $resourceName, int $currentCount): bool
    {
        $activeSubscription = $store->subscriptions()->whereIn('status', ['active', 'trialing'])->first();
        
        if (!$activeSubscription) {
            return false; // No active plan = strict block
        }

        $plan = $activeSubscription->plan;

        switch ($resourceName) {
            case 'products':
                return $currentCount < $plan->max_products;
            case 'pages':
                return $currentCount < $plan->max_pages;
            default:
                // Feature check
                $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true);
                if (isset($features[$resourceName])) {
                    return filter_var($features[$resourceName], FILTER_VALIDATE_BOOLEAN);
                }
                return false;
        }
    }

    /**
     * Upgrade or Downgrade a plan (simple proration calculation logic stub for SSLCommerz hook)
     */
    public function changePlan(Store $store, Plan $newPlan)
    {
        $activeSubscription = $store->subscriptions()->whereIn('status', ['active', 'trialing'])->first();
        if (!$activeSubscription) {
            // Treat as fresh subscription purchase
            return $this->startActiveSubscription($store, $newPlan);
        }

        // Handle upgrade/downgrade logic
        // This is where we calculate unused days, format invoice, and send to SSLCommerz
        // For simplicity in foundational setup: immediately transition
        
        $activeSubscription->update([
            'status' => 'canceled',
            'canceled_at' => Carbon::now(),
            'ends_at' => Carbon::now()
        ]);

        return $store->subscriptions()->create([
            'plan_id' => $newPlan->id,
            'status' => 'active',
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonth(), // Assuming monthly standard
        ]);
    }

    private function startActiveSubscription(Store $store, Plan $plan)
    {
        return $store->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonth(),
        ]);
    }
}
