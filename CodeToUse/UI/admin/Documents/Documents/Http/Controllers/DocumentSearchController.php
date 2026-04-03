<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Support\TenantResolver;
use Modules\Documents\Services\Search\DocumentSearchService;
use Modules\Documents\Services\Search\SavedViewService;
use Modules\Documents\Entities\DocumentSavedView;

class DocumentSearchController extends Controller
{
    public function index(Request $request, DocumentSearchService $search, SavedViewService $savedViews)
    {
        $companyId = $request->user()?->tenant_id;
        $savedViews->systemDefaults($companyId, $request->user()?->id);

        $filters = $request->only(['q','type','status','created_by','updated_from','updated_to','sort','dir']);
        $filters['tenant_id'] = $companyId;

        $docs = $search->query($filters)->paginate(20)->withQueryString();

        $views = DocumentSavedView::query()
            ->where(function($q) use ($companyId) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', $companyId);
            })
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        return view('documents::documents.search.index', [
            'documents' => $docs,
            'filters' => $filters,
            'views' => $views,
        ]);
    }
}
