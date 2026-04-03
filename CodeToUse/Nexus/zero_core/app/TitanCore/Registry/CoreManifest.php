<?php

namespace App\TitanCore\Registry;

use App\TitanCore\Registry\Runtime\RuntimeCatalog;
use App\TitanCore\Registry\Tools\ToolRegistry;
use App\TitanCore\Support\CoreSourceCatalog;

class CoreManifest
{
    public function __construct(
        protected CoreModuleRegistry $modules,
        protected CoreSourceCatalog $sources,
        protected RuntimeCatalog $runtimes,
        protected ToolRegistry $tools,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'modules' => $this->modules->manifest(),
            'sources' => $this->sources->extractionOrder(),
            'runtimes' => $this->runtimes->manifest(),
            'tools' => $this->tools->manifest(),
        ];
    }
}
