<?php

namespace Database\Factories\Support;

use App\Models\Support\ServiceIssue;
use App\Models\Support\ServiceIssueMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceIssueMessage>
 */
class ServiceIssueMessageFactory extends Factory
{
    protected $model = ServiceIssueMessage::class;

    public function definition(): array
    {
        $issue = ServiceIssue::factory()->create();
        $user = User::factory()->create(['company_id' => $issue->company_id]);

        return [
            'service_issue_id' => $issue->id,
            'company_id'       => $issue->company_id,
            'user_id'          => $user->id,
            'is_internal'      => false,
            'message'          => $this->faker->sentence(),
            'attachments'      => [],
        ];
    }
}
