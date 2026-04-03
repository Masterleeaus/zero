<?php

namespace Modules\Documents\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentTemplate;
use Modules\Documents\Entities\DocumentShareLink;
use Modules\Documents\Policies\DocumentPolicy;
use Modules\Documents\Policies\DocumentTemplatePolicy;
use Modules\Documents\Policies\DocumentShareLinkPolicy;
use Modules\Documents\Policies\DocumentTemplateGovernancePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DocumentPolicy::class,

        // Template viewing permissions (existing)
        DocumentTemplate::class => DocumentTemplatePolicy::class,

        // Premium governance
        DocumentShareLink::class => DocumentShareLinkPolicy::class,
        // Governance is checked directly (manage/publish/unpublish) in controllers as well
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
