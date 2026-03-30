<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\actingAs;

it('returns company id when present', function () {
    $user = User::factory()->create(['company_id' => 10, 'team_id' => 20]);

    actingAs($user);

    expect(tenant())->toBe(10);
});

it('falls back to team id when company id is missing', function () {
    $user = User::withoutEvents(fn () => User::factory()->create(['company_id' => null, 'team_id' => 20]));

    actingAs($user);

    expect(tenant())->toBe(20);
});

it('returns null when there is no authenticated user', function () {
    Auth::logout();

    expect(tenant())->toBeNull();
});
