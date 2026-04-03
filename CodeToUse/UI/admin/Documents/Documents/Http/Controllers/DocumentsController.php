<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Entities\DocumentTemplate;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentFile;
use Modules\Documents\Http\Requests\StoreDocumentRequest;
use Modules\Documents\Http\Requests\UpdateDocumentRequest;
use Modules\Documents\Support\QrBridge;
use Modules\Documents\Support\TenantResolver;

class DocumentsController extends Controller
{
    public function index()
    {
        return redirect()->route('documents.general');
    }

    public function indexGeneral()
    {
        $tenantId = TenantResolver::id();

        $docs = Document::general()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('documents::documents.index_general', compact('docs'));
    }

    public function indexSwms()
    {
        $tenantId = TenantResolver::id();

        $docs = Document::swms()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('documents::documents.index_swms', compact('docs'));
    }

    public function create(Request $request)
    {
        $tpl = null;

        if ($request->filled('template')) {
            $tpl = DocumentTemplate::where('slug', $request->get('template'))->first();
            if ($tpl) {
                // Prefill the form with template content
                session()->flash('_old_input', array_merge(old(), [
                    'body_markdown' => $tpl->body_markdown,
                    'title'         => $tpl->name,
                    'type'          => $tpl->category === 'SWMS' ? 'swms' : 'general',
                    'template_slug' => $tpl->slug,
                ]));
            }
        }

        return view('documents::documents.create', compact('tpl'));
    }

    public function show(Document $document)
    {
        $tenantId = TenantResolver::id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $document->load(['files', 'sections', 'metadata', 'shareLinks', 'links', 'versions', 'statusHistory']);

        return view('documents::documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $tenantId = TenantResolver::id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $document->load(['sections', 'metadata', 'shareLinks', 'links', 'versions', 'statusHistory']);

        return view('documents::documents.edit', compact('document'));
    }

    public function store(StoreDocumentRequest $request)
    {
        $tenantId = TenantResolver::id();

        $validated = $request->validated();

        $doc = new Document();
        $doc->tenant_id    = $tenantId;
        $doc->title        = $validated['title'];
        $doc->type         = $validated['type'] ?? 'general';
        $doc->category     = $validated['category'] ?? null;
        $doc->subcategory  = $validated['subcategory'] ?? null;
        $doc->template_slug= $validated['template_slug'] ?? null;
        $doc->body_markdown= $validated['body_markdown'] ?? '';
        $doc->body_html    = null;
        $doc->status       = 'draft';
        $doc->qr_slug      = null;
        $doc->save();

        // QR slug and QRTrack integration
        $doc->qr_slug = 'doc-' . $doc->id;
        $doc->save();

        QrBridge::ensureCode($doc->qr_slug, [
            'label'        => $doc->title,
            'type'         => $doc->type === 'swms' ? 'swms' : 'link',
            'redirect_url' => route('documents.public.show', $doc->id),
            'tag'          => $doc->type,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $disk = 'local';

            foreach ($request->file('attachments') as $uploadedFile) {
                if (! $uploadedFile) {
                    continue;
                }

                $path = $uploadedFile->store("documents/{$tenantId}/attachments", $disk);

                DocumentFile::create([
                    'tenant_id'     => $tenantId,
                    'document_id'   => $doc->id,
                    'folder_id'     => null,
                    'name'          => $uploadedFile->getClientOriginalName(),
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'disk'          => $disk,
                    'path'          => $path,
                    'mime_type'     => $uploadedFile->getClientMimeType(),
                    'size'          => $uploadedFile->getSize(),
                    'is_public'     => true,
                    'uploaded_by'   => auth()->user()?->company_id,
                ]);
            }
        }

        return redirect()
            ->route($doc->type === 'swms' ? 'documents.swms' : 'documents.general')
            ->with('status', __('Document saved.'));
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $tenantId = TenantResolver::id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $validated = $request->validated();

        $document->fill([
            'title' => $validated['title'],
            'type'  => $validated['type'] ?? $document->type,
            'category' => $validated['category'] ?? null,
            'subcategory' => $validated['subcategory'] ?? null,
            'template_slug' => $validated['template_slug'] ?? null,
            'body_markdown' => $validated['body_markdown'] ?? '',
            'status' => $validated['status'] ?? $document->status,
        ]);
        $document->save();

        return redirect()->route('documents.show', $document)->with('status', __('Document updated.'));
    }

    public function destroy(Document $document)
    {
        $tenantId = TenantResolver::id();
        abort_unless((int) $document->tenant_id === (int) $tenantId, 404);

        $document->files()->delete();
        $document->sections()->delete();
        $document->metadata()->delete();
        $document->delete();

        return redirect()->route('documents.general')->with('status', __('Document deleted.'));
    }

    public function templates(Request $request)
    {
        $tenantId = TenantResolver::id();

        $tab = $request->get('tab', 'docs');
        $tab = in_array($tab, ['docs', 'swms'], true) ? $tab : 'docs';

        $q = trim((string) $request->get('q', ''));

        $trade = trim((string) $request->get('trade', ''));

        $base = DocumentTemplate::query()
            ->where(function ($q2) use ($tenantId) {
                $q2->whereNull('tenant_id')
                   ->orWhere('tenant_id', $tenantId);
            });

        $counts = [
            'docs' => (clone $base)->where(function ($w) {
                $w->whereNull('category')->orWhere('category', '!=', 'SWMS');
            })->count(),
            'swms' => (clone $base)->where('category', 'SWMS')->count(),
        ];

        $templatesQuery = (clone $base);

        $tradeOptions = (clone $base)
            ->whereNotNull('trade')
            ->select('trade')
            ->distinct()
            ->orderBy('trade')
            ->pluck('trade')
            ->toArray();

        if ($tab === 'swms') {
            $templatesQuery->where('category', 'SWMS');
        } else {
            $templatesQuery->where(function ($w) {
                $w->whereNull('category')->orWhere('category', '!=', 'SWMS');
            });
        }

        if ($q !== '') {
            $templatesQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%");
            });
        }

        $templates = $templatesQuery->orderBy('name')->get();

        return view('documents::documents.templates', [
            'templates' => $templates,
            'tab' => $tab,
            'counts' => $counts,
            'q' => $q,
            'trade' => $trade,
            'tradeOptions' => $tradeOptions,
        ]);
    }


    public function applyTemplate(Request $request)
    {
        $data = $request->validate([
            'template' => 'required|string',
            'vars'     => 'nullable|string',
        ]);

        $tpl = DocumentTemplate::where('slug', $data['template'])->firstOrFail();

        $body = $tpl->body_markdown ?? '';
        $pairs = [];

        foreach (preg_split("/\r\n|\n|\r/", (string) ($data['vars'] ?? '')) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $pairs[trim($k)] = trim($v);
        }

        foreach ($pairs as $k => $v) {
            $body = str_replace('{{'.$k.'}}', $v, $body);
        }

        return back()->withInput([
            'title'         => $tpl->name,
            'body_markdown' => $body,
            'vars'          => $data['vars'],
            'type'          => $tpl->category === 'SWMS' ? 'swms' : 'general',
            'template_slug' => $tpl->slug,
        ]);
    }

    public function printTemplate($slug)
    {
        $tpl = DocumentTemplate::where('slug', $slug)->firstOrFail();

        QrBridge::ensureCode('tpl-' . $tpl->slug, [
            'label'        => $tpl->name,
            'type'         => 'template',
            'redirect_url' => route('documents.templates'),
            'tag'          => 'template',
        ]);

        return response()->view('documents::documents.template_print', compact('tpl'));
    }

    public function publicShow($id)
    {
        $doc = Document::with('files')->findOrFail($id);

        return response()->view('documents::documents.public_show', compact('doc'));
    }
}
