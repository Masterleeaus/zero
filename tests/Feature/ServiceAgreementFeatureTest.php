<?php

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceAgreementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_agreements_are_company_scoped_and_link_jobs(): void
    {
        $user = User::factory()->create(['company_id' => 5]);
        $customer = Customer::factory()->create(['company_id' => 5]);

        $agreement = ServiceAgreement::factory()->create([
            'company_id'  => 5,
            'customer_id' => $customer->id,
        ]);

        ServiceJob::factory()->create([
            'company_id'    => 5,
            'agreement_id'  => $agreement->id,
            'customer_id'   => $customer->id,
        ]);

        $other = ServiceAgreement::factory()->create(['company_id' => 6]);

        $resp = $this->actingAs($user)->get(route('dashboard.work.agreements.index'));
        $resp->assertStatus(200);
        $this->assertTrue($resp->viewData('agreements')->contains('id', $agreement->id));
        $this->assertFalse($resp->viewData('agreements')->contains('id', $other->id));
    }
}
