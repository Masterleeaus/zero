<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Services\Workflow\DocumentWorkflowService;

class DocumentWorkflowController extends Controller
{
    public function __construct(protected DocumentWorkflowService $workflow)
    {
    }

    public function submitForReview(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $this->workflow->changeStatus($document, 'review', auth()->id(), $request->input('note'));

        return back()->with('status', __('Sent for review.'));
    }

    public function approve(Request $request, Document $document)
    {
        $this->authorize('approve', $document);
        $this->workflow->changeStatus($document, 'approved', auth()->id(), $request->input('note'));

        return back()->with('status', __('Approved.'));
    }

    public function archive(Request $request, Document $document)
    {
        $this->authorize('archive', $document);
        $this->workflow->changeStatus($document, 'archived', auth()->id(), $request->input('note'));

        return back()->with('status', __('Archived.'));
    }

    public function revertToDraft(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $this->workflow->changeStatus($document, 'draft', auth()->id(), $request->input('note'));

        return back()->with('status', __('Moved to draft.'));
    }
}
