<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotes_are_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 11]);
        Quote::factory()->create(['company_id' => 11, 'number' => 'Q-1111']);
        Quote::factory()->create(['company_id' => 12, 'number' => 'Q-2222']);

        $this->actingAs($user);

        $this->get(route('dashboard.money.quotes.index'))
            ->assertOk()
            ->assertSee('Q-1111')
            ->assertDontSee('Q-2222');
    }

    public function test_invoices_are_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 11]);
        Invoice::factory()->create(['company_id' => 11, 'number' => 'INV-1111']);
        Invoice::factory()->create(['company_id' => 12, 'number' => 'INV-2222']);

        $this->actingAs($user);

        $this->get(route('dashboard.money.invoices.index'))
            ->assertOk()
            ->assertSee('INV-1111')
            ->assertDontSee('INV-2222');
    }
}
