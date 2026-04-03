<?php

namespace App\TitanCore\Zero\Knowledge;

class KnowledgeScopeResolver
{
    /**
     * @param  array<string, mixed>  $envelope
     */
    public function scope(array $envelope): string
    {
        if (! empty($envelope['site_id'])) {
            return 'site';
        }

        if (! empty($envelope['job_id'])) {
            return 'job';
        }

        if (! empty($envelope['company_id'])) {
            return (string) config('titan_core.knowledge.default_scope', 'tenant');
        }

        return 'global';
    }
}
