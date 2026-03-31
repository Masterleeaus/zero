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
        return [
            'service_issue_id' => static fn () => ServiceIssue::factory()->create()->id,
            'company_id'       => static function (array $attributes) {
                if (isset($attributes['company_id'])) {
                    return $attributes['company_id'];
                }

                if (isset($attributes['service_issue_id'])) {
                    return ServiceIssue::find($attributes['service_issue_id'])?->company_id;
                }

                return null;
            },
            'user_id'          => static function (array $attributes) {
                if (isset($attributes['user_id'])) {
                    return $attributes['user_id'];
                }

                if (isset($attributes['company_id'])) {
                    return User::factory()->create(['company_id' => $attributes['company_id']])->id;
                }

                return User::factory()->create()->id;
            },
            'is_internal'      => false,
            'message'          => $this->faker->sentence(),
            'attachments'      => [],
        ];
    }
}
