<?php

namespace Modules\TitanHello\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\TitanHello\Models\Intent;
use Modules\TitanHello\Models\Entity;
use Modules\TitanHello\Models\TrainingPhrase;
use Modules\TitanHello\Policies\IntentPolicy;
use Modules\TitanHello\Policies\EntityPolicy;
use Modules\TitanHello\Policies\TrainingPhrasePolicy;

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
