<?php

namespace Modules\Documents\DTO;

class TitanZeroRequest
{
    public function __construct(
        public string $intent,
        public array $page,
        public array $record,
        public array $fields,
        public ?string $returnUrl = null,
        public ?string $heroKey = null,
    ) {
    }
}
