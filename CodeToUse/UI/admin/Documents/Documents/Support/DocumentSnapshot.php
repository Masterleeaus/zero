<?php

namespace Modules\Documents\Support;

use Modules\Documents\Entities\Document;

class DocumentSnapshot
{
    public static function fromDocument(Document $document): array
    {
        return [
            'title' => $document->title,
            'type' => $document->type,
            'status' => $document->status,
            'category' => $document->category,
            'subcategory' => $document->subcategory,
            'template_slug' => $document->template_slug,
            'body_markdown' => $document->body_markdown,
            'trade' => $document->trade,
            'role' => $document->role,
            'effective_at' => optional($document->effective_at)->toISOString(),
            'review_at' => optional($document->review_at)->toISOString(),
            'sections' => $document->sections?->map(fn($s) => [
                'key' => $s->key,
                'label' => $s->label,
                'content' => $s->content,
                'sort_order' => $s->sort_order,
            ])->values()->all(),
            'metadata' => $document->metadata?->pluck('meta_value', 'meta_key')->all(),
        ];
    }
}
