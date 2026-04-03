<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Support\TenantResolver;
use Modules\Documents\Entities\DocumentSavedView;
use Modules\Documents\Http\Requests\SaveDocumentViewRequest;

class DocumentSavedViewsController extends Controller
{
    public function store(SaveDocumentViewRequest $request)
    {
        $data = $request->validated();
        $companyId = $request->user()?->tenant_id;

        DocumentSavedView::create([
            'tenant_id' => $companyId,
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'filters' => $data['filters'] ?? [],
            'is_system' => false,
        ]);

        return back()->with('status', 'Saved view created.');
    }

    public function destroy(Request $request, DocumentSavedView $view)
    {
        abort_unless($request->user(), 403);

        // Only allow deleting user-owned views
        abort_unless(!$view->is_system && (int)$view->user_id === (int)$request->user()->id, 403);

        $view->delete();

        return back()->with('status', 'Saved view deleted.');
    }
}
