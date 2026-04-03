<?php

namespace Modules\Documents\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentTemplate;
use Modules\Documents\Policies\DocumentPolicy;
use Modules\Documents\Policies\DocumentTemplatePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DocumentPolicy::class,
        DocumentTemplate::class => DocumentTemplatePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
