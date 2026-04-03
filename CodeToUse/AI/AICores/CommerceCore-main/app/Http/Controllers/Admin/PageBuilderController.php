<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuilderPage;
use App\Models\BuilderSection;
use App\Models\Store;
use App\Services\BuilderService;
use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    public function __construct(private BuilderService $builderService) {}

    private function resolveStore(Request $request): Store
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            $storeId = $request->input('store_id', session('admin_store_id'));
            return $storeId ? Store::findOrFail($storeId) : Store::firstOrFail();
        }
        return $request->get('admin_store') ?? $user->ownedStores()->firstOrFail();
    }

    public function index(Request $request)
    {
        $store = $this->resolveStore($request);
        $pages = BuilderPage::where('store_id', $store->id)
            ->withCount('sections')
            ->orderBy('sort_order')
            ->get();

        return view('admin.builder.index', compact('pages', 'store'));
    }

    public function createPage(Request $request)
    {
        $store = $this->resolveStore($request);
        return view('admin.builder.create-page', compact('store'));
    }

    public function storePage(Request $request)
    {
        $store = $this->resolveStore($request);

        $validated = $request->validate([
            'page_name' => 'required|string|max:255',
            'is_homepage' => 'boolean',
        ]);

        $validated['is_homepage'] = $request->boolean('is_homepage');

        $page = $this->builderService->createPage($store, $validated);

        return redirect()->route('admin.builder.edit', $page)
            ->with('success', 'Page created!');
    }

    public function edit(BuilderPage $page)
    {
        $page->load('sections.contents');
        $sectionTypes = BuilderSection::sectionTypes();
        $store = $page->store;

        return view('admin.builder.edit', compact('page', 'sectionTypes', 'store'));
    }

    public function addSection(Request $request, BuilderPage $page)
    {
        $validated = $request->validate([
            'type' => 'required|in:hero,product_grid,banner,text_block,cta,features,testimonials,faq,newsletter,contact,custom_code',
        ]);

        $this->builderService->addSection($page, $validated['type']);

        return redirect()->route('admin.builder.edit', $page)
            ->with('success', 'Section added!');
    }

    public function updateSection(Request $request, BuilderSection $section)
    {
        $contents = $request->input('contents', $request->except('_token', '_method'));
        $this->builderService->updateSectionContent($section, $contents);

        return redirect()->back()->with('success', 'Architect commitment successful!');
    }

    public function toggleSection(BuilderSection $section)
    {
        $this->builderService->toggleSection($section);
        return redirect()->back()->with('success', 'Section toggled!');
    }

    public function deleteSection(BuilderSection $section)
    {
        $pageId = $section->page_id;
        $this->builderService->deleteSection($section);

        return redirect()->route('admin.builder.edit', $pageId)
            ->with('success', 'Section deleted!');
    }

    public function reorder(Request $request, BuilderPage $page)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:builder_sections,id',
        ]);

        $this->builderService->reorderSections($page, $validated['order']);

        return response()->json(['success' => true]);
    }

    public function preview(BuilderPage $page)
    {
        $page->load('activeSections.contents', 'store');
        $store = $page->store;

        return view('storefront.preview', compact('page', 'store'));
    }

    public function deletePage(BuilderPage $page)
    {
        $page->delete();
        return redirect()->route('admin.builder.index')
            ->with('success', 'Page deleted!');
    }
}
