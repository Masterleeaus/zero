<?php

namespace Modules\Documents\Services;

use Illuminate\Support\Str;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentTag;

class TagService
{
    public function createOrUpdateTag(array $data): DocumentTag
    {
        $tenantId = documents_tenant_id();
        $name = trim((string)($data['name'] ?? ''));
        $slug = $data['slug'] ?? Str::slug($name);

        return DocumentTag::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => $slug],
            [
                'tenant_id' => $tenantId,
                'name' => $name,
                'slug' => $slug,
                'bg_color' => $data['bg_color'] ?? null,
                'text_color' => $data['text_color'] ?? null,
            ]
        );
    }

    public function attachTag(Document $document, int $tagId): void
    {
        $tenantId = documents_tenant_id();
        $document->tags()->syncWithoutDetaching([
            $tagId => ['tenant_id' => $tenantId],
        ]);
    }

    public function detachTag(Document $document, int $tagId): void
    {
        $document->tags()->detach($tagId);
    }
}
