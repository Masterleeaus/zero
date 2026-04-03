<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentSection;
use Modules\Documents\Http\Requests\StoreSectionRequest;

class DocumentSectionsController extends Controller
{
    public function store(StoreSectionRequest $request, Document $document)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $data = $request->validated();

        DocumentSection::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'document_id' => $document->id,
                'key' => $data['key'],
            ],
            [
                'label' => $data['label'],
                'content' => $data['content'] ?? null,
                'sort_order' => $data['order'] ?? 0,
            ]
        );

        return back()->with('status', __('Section saved.'));
    }

    public function destroy(Document $document, DocumentSection $section)
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);
        abort_unless((int) $section->document_id === (int) $document->id, 404);

        $section->delete();

        return back()->with('status', __('Section removed.'));
    }
}
