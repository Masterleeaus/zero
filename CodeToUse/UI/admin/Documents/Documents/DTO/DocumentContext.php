<?php

namespace Modules\Documents\DTO;

class DocumentContext
{
    public function __construct(
        public ?int $tenantId,
        public ?int $userId,
        public ?string $routeName,
        public ?string $url,
        public ?string $recordType,
        public ?int $recordId,
        public array $fields = [],
    ) {}

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'page' => ['route_name' => $this->routeName, 'url' => $this->url],
            'record' => ['record_type' => $this->recordType, 'record_id' => $this->recordId],
            'fields' => $this->fields,
        ];
    }
}
