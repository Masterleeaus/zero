<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaaSBillingTest extends TestCase
{
    use RefreshDatabase;

    private $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionService = app(SubscriptionService::class);
    }

    public function test_trial_starts_for_new_store()
    {
        // 1. Setup Data
        $owner = User::factory()->create(['role' => 'store_owner']);
        $plan = Plan::create([
            'name' => 'Pro+',
            'slug' => 'pro-plus',
            'price' => 49.00,
            'max_products' => 5000,
            'is_active' => true,
        ]);

        $store = Store::create([
            'owner_id' => $owner->id,
            'name' => 'Demo Store',
            'slug' => 'demo-store',
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        // 2. Action: Start Trial
        $subscription = $this->subscriptionService->startTrial($store, $plan);

        // 3. Assertions
        $this->assertEquals('trialing', $subscription->status);
        $this->assertTrue(Carbon::parse($subscription->trial_ends_at)->isAfter(Carbon::now()));
        $this->assertEquals($plan->id, $subscription->plan_id);
    }

    public function test_resource_limit_checks()
    {
        // 1. Setup Store with Trial
        $owner = User::factory()->create(['role' => 'store_owner']);
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 99,
            'max_products' => 500, // Limit
            'max_pages' => 10,
            'is_active' => true,
        ]);
        
        $store = Store::factory()->create(['owner_id' => $owner->id, 'plan_id' => $plan->id]);
        $this->subscriptionService->startTrial($store, $plan);

        // 2. Actions & Assertions
        // Store currently has 0 products, 500 is the limit
        $this->assertTrue($this->subscriptionService->checkResourceLimit($store, 'products', 0));
        
        // At the limit
        $this->assertFalse($this->subscriptionService->checkResourceLimit($store, 'products', 500));
        
        // Over the limit
        $this->assertFalse($this->subscriptionService->checkResourceLimit($store, 'products', 501));
    }

    public function test_plan_upgrade_updates_subscription()
    {
        // 1. Setup Current Plan
        $owner = User::factory()->create(['role' => 'store_owner']);
        $basicPlan = Plan::factory()->create(['price' => 29, 'slug' => 'basic']);
        $proPlan = Plan::factory()->create(['price' => 99, 'slug' => 'pro']);
        
        $store = Store::factory()->create(['owner_id' => $owner->id, 'plan_id' => $basicPlan->id]);
        $this->subscriptionService->startTrial($store, $basicPlan);

        // 2. Action: Upgrade Plan
        $newSubscription = $this->subscriptionService->changePlan($store, $proPlan);

        // 3. Assertions
        $this->assertEquals('active', $newSubscription->status);
        $this->assertEquals($proPlan->id, $newSubscription->plan_id);
        
        // Old subscription should be canceled
        $this->assertCount(2, Subscription::where('store_id', $store->id)->get());
        $this->assertCount(1, Subscription::where('store_id', $store->id)->where('status', 'canceled')->get());
    }
}
