<?php

namespace Modules\Documents\Services\TitanZero;

use Modules\Documents\Entities\Document;

class ContextPackBuilder
{
    /**
     * Build a curated fields snapshot for Titan Zero/Heroes. Do NOT include secrets or large blobs.
     */
    public function buildForDocument(?Document $document, array $overrides = []): array
    {
        $base = [
            'title' => $document?->title,
            'type' => $document?->type,
            'status' => $document?->status,
            'category' => $document?->category,
            'subcategory' => $document?->subcategory,
            'body_markdown' => $document?->body_markdown,
            'effective_at' => $document?->effective_at?->toDateString(),
            'review_at' => $document?->review_at?->toDateString(),
        ];

        return array_merge(array_filter($base, fn($v) => $v !== null), $overrides);
    }
}
