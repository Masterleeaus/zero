<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Common\Menu;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_and_expense_routes_are_seeded_into_menu(): void
    {
        $this->seed(MenuSeeder::class);

        $this->assertTrue(Menu::query()->where('key', 'operations_leaves')->exists());
        $this->assertTrue(Menu::query()->where('key', 'money_expenses')->exists());
        $this->assertTrue(Menu::query()->where('key', 'money_expense_categories')->exists());
    }
}
