<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\DocumentTag;
use Modules\Documents\Services\TagService;

class TagsController extends Controller
{
    public function __construct(private TagService $tags) {}

    public function index()
    {
        $this->authorize('viewAny', DocumentTag::class);

        $tags = DocumentTag::query()->orderBy('name')->paginate(50);

        return view('documents::tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DocumentTag::class);

        $request->validate([
            'name' => 'required|string|max:120',
            'bg_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
        ]);

        $this->tags->createOrUpdateTag($request->all());

        return back()->with('success', __('Tag saved.'));
    }

    public function destroy(DocumentTag $tag)
    {
        $this->authorize('delete', $tag);
        $tag->delete();

        return back()->with('success', __('Tag deleted.'));
    }
}
