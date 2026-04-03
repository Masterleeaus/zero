<?php

namespace Modules\Documents\Services;

use Illuminate\Support\Arr;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentFolder;
use Modules\Documents\Entities\Template;

class OrderingService
{
    public function reorderDocuments(array $ids): void
    {
        $tenantId = documents_tenant_id();
        foreach (array_values($ids) as $i => $id) {
            Document::query()->where('tenant_id', $tenantId)->where('id', $id)->update(['position' => $i + 1]);
        }
    }

    public function reorderFolders(array $ids): void
    {
        $tenantId = documents_tenant_id();
        foreach (array_values($ids) as $i => $id) {
            DocumentFolder::query()->where('tenant_id', $tenantId)->where('id', $id)->update(['position' => $i + 1]);
        }
    }

    public function reorderTemplates(array $ids): void
    {
        $tenantId = documents_tenant_id();
        foreach (array_values($ids) as $i => $id) {
            Template::query()->where('tenant_id', $tenantId)->where('id', $id)->update(['position' => $i + 1]);
        }
    }
}
