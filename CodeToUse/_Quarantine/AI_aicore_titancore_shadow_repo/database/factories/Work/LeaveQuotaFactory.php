<?php

namespace Database\Factories\Work;

use App\Models\User;
use App\Models\Work\LeaveQuota;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveQuotaFactory extends Factory
{
    protected $model = LeaveQuota::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'company_id'        => $user->company_id,
            'user_id'           => $user->id,
            'annual_allowance'  => 20,
            'sick_allowance'    => 10,
            'custom_allowance'  => 5,
        ];
    }
}
