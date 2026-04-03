<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\TitanTalk\Models\Intent;
use Modules\TitanTalk\Models\Entity;
use Modules\TitanTalk\Models\TrainingPhrase;
use Modules\TitanTalk\Policies\IntentPolicy;
use Modules\TitanTalk\Policies\EntityPolicy;
use Modules\TitanTalk\Policies\TrainingPhrasePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Intent::class => IntentPolicy::class,
        Entity::class => EntityPolicy::class,
        TrainingPhrase::class => TrainingPhrasePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
