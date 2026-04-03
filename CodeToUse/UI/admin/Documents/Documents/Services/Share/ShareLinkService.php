<?php

namespace Modules\Documents\Services\Share;

use Modules\Documents\Entities\DocumentShareLink;
use Illuminate\Support\Str;

class ShareLinkService
{
    public function create(int $documentId, ?int $companyId, ?\DateTimeInterface $expiresAt = null, ?int $maxViews = null): DocumentShareLink
    {
        // Token must be unguessable
        $token = Str::random(48);

        return DocumentShareLink::query()->create([
            'document_id' => $documentId,
            'company_id' => $companyId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'max_views' => $maxViews,
            'views_count' => 0,
        ]);
    }

    public function revoke(DocumentShareLink $link, int $userId): void
    {
        $link->revoked_at = now();
        $link->revoked_by = $userId;
        $link->save();
    }
}
