<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentTemplateAdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('documents.templates.manage') || auth()->user()?->can('manage_documents'), 403);

        $q = $request->get('q');
        $query = DocumentTemplate::query();

        if ($q) {
            $query->where('title', 'like', '%'.$q.'%')
                ->orWhere('description', 'like', '%'.$q.'%')
                ->orWhere('category', 'like', '%'.$q.'%');
        }

        $templates = $query->orderByDesc('published_at')->paginate(20);

        return view('documents::templates.admin.index', compact('templates', 'q'));
    }

    public function edit($id)
    {
        abort_unless(auth()->user()?->can('documents.templates.manage') || auth()->user()?->can('manage_documents'), 403);

        $template = DocumentTemplate::query()->findOrFail($id);
        return view('documents::templates.admin.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()?->can('documents.templates.manage') || auth()->user()?->can('manage_documents'), 403);

        $template = DocumentTemplate::query()->findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'trade' => 'nullable|string|max:50',
            'role_key' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $template->update($data);

        return redirect()->route('documents.templates.admin.index')
            ->with('success', 'Template updated.');
    }
}
