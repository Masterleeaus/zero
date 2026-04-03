<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentTemplatePublishController extends Controller
{
    public function publish($id)
    {
        abort_unless(auth()->user()?->can('documents.templates.manage') || auth()->user()?->can('manage_documents'), 403);

        $template = DocumentTemplate::query()->findOrFail($id);
        $template->published_at = now();
        $template->published_by = auth()->id();
        $template->save();

        return back()->with('success', 'Template published.');
    }

    public function unpublish($id)
    {
        abort_unless(auth()->user()?->can('documents.templates.manage') || auth()->user()?->can('manage_documents'), 403);

        $template = DocumentTemplate::query()->findOrFail($id);
        $template->published_at = null;
        $template->save();

        return back()->with('success', 'Template unpublished.');
    }
}
