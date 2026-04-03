<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentManagerController extends Controller
{
    public function dashboard()
    {
        $tenantId = function_exists('company') && company() ? company()->id : auth()->id();

        $myDocuments = Document::general()
            ->where('tenant_id', $tenantId)
            ->count();

        $mySwms = Document::swms()
            ->where('tenant_id', $tenantId)
            ->count();

        // For now, company metrics mirror tenant metrics
        $companyDocuments = $myDocuments;
        $companySwms = $mySwms;

        $recentTemplates = DocumentTemplate::where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id')
                  ->orWhere('tenant_id', $tenantId);
            })
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('documents::document_manager.dashboard', compact(
            'myDocuments',
            'mySwms',
            'companyDocuments',
            'companySwms',
            'recentTemplates'
        ));
    }
}
