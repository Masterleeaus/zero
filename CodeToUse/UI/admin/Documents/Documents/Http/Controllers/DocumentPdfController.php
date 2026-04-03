<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;

class DocumentPdfController extends Controller
{
    public function export(Request $request, $id)
    {
        abort_unless(auth()->user()?->can('documents.export') || auth()->user()?->can('manage_documents'), 403);

        $doc = Document::query()->findOrFail($id);

        // Render HTML view (works even if PDF libs not installed)
        $view = $doc->type === 'swms'
            ? 'documents::pdf.swms'
            : 'documents::pdf.document';

        $html = view($view, ['document' => $doc])->render();

        // If dompdf is available, stream a PDF
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download('document-'.$doc->id.'.pdf');
        }

        // Fallback: return HTML preview
        return response($html);
    }

    public function preview(Request $request, $id)
    {
        abort_unless(auth()->user()?->can('documents.export') || auth()->user()?->can('manage_documents'), 403);

        $doc = Document::query()->findOrFail($id);

        $view = $doc->type === 'swms'
            ? 'documents::pdf.swms'
            : 'documents::pdf.document';

        return view($view, ['document' => $doc]);
    }
}
