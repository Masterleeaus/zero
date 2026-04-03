<?php

namespace App\TitanCore\MCP\Handlers;

use App\TitanCore\Zero\AI\TitanAIRouter;

class AiCompleteHandler
{
    public function __construct(protected TitanAIRouter $router)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        return $this->router->execute($params);
    }
}
