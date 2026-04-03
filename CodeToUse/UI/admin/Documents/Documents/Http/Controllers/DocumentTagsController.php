<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\Document;
use Modules\Documents\Services\TagService;

class DocumentTagsController extends Controller
{
    public function __construct(private TagService $tags) {}

    public function store(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'tag_id' => 'required|integer',
        ]);

        $this->tags->attachTag($document, (int)$request->input('tag_id'));

        return back()->with('success', __('Tag added.'));
    }

    public function destroy(Document $document, int $tagId)
    {
        $this->authorize('update', $document);

        $this->tags->detachTag($document, $tagId);

        return back()->with('success', __('Tag removed.'));
    }
}
