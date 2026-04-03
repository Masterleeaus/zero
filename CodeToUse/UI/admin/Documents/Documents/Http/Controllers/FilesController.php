<?php

namespace Modules\Documents\Http\Controllers;

use Modules\Documents\Support\TenantResolver;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Entities\DocumentFile;
use Modules\Documents\Entities\DocumentFolder;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = TenantResolver::id();

        $folderId = $request->get('folder_id');

        $currentFolder = $folderId
            ? DocumentFolder::where('tenant_id', $tenantId)->findOrFail($folderId)
            : null;

        $rootFolders = DocumentFolder::where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $files = DocumentFile::where('tenant_id', $tenantId)
            ->when($currentFolder, function ($query, $currentFolder) {
                return $query->where('folder_id', $currentFolder->id);
            })
            ->orderBy('name')
            ->paginate(20);

        $breadcrumbs = [];
        $tmp = $currentFolder;
        while ($tmp) {
            $breadcrumbs[] = $tmp;
            $tmp = $tmp->parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);

        return view('documents::files.index', compact(
            'rootFolders',
            'currentFolder',
            'files',
            'breadcrumbs'
        ));
    }

    public function create(Request $request)
    {
        $folderId = $request->get('folder_id');

        return view('documents::files.create', compact('folderId'));
    }

    public function store(Request $request)
    {
        $tenantId = TenantResolver::id();

        $data = $request->validate([
            'folder_id' => 'nullable|integer|exists:document_folders,id',
            'file'      => 'required|file|max:10240',
            'is_public' => 'nullable|boolean',
        ]);

        $folderId = $data['folder_id'] ?? null;
        $file     = $data['file'];

        $disk = 'local';
        $path = $file->store("documents/{$tenantId}", $disk);

        DocumentFile::create([
            'tenant_id'     => $tenantId,
            'folder_id'     => $folderId,
            'document_id'   => null,
            'name'          => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'disk'          => $disk,
            'path'          => $path,
            'mime_type'     => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'is_public'     => (bool) ($data['is_public'] ?? false),
            'uploaded_by'   => auth()->id(),
        ]);

        return redirect()
            ->route('documents.files.index', ['folder_id' => $folderId])
            ->with('success', __('File uploaded.'));
    }

    public function edit(DocumentFile $file)
    {
        $this->authorizeTenant($file);

        return view('documents::files.edit', compact('file'));
    }

    public function update(Request $request, DocumentFile $file)
    {
        $this->authorizeTenant($file);

        $data = $request->validate([
            'name'      => 'required|string|max:190',
            'is_public' => 'nullable|boolean',
        ]);

        $file->update([
            'name'      => $data['name'],
            'is_public' => (bool) ($data['is_public'] ?? false),
        ]);

        return back()->with('success', __('File updated.'));
    }

    public function destroy(DocumentFile $file)
    {
        $this->authorizeTenant($file);

        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $folderId = $file->folder_id;

        $file->delete();

        return redirect()
            ->route('documents.files.index', ['folder_id' => $folderId])
            ->with('success', __('File deleted.'));
    }

    protected function authorizeTenant(DocumentFile $file): void
    {
        $tenantId = TenantResolver::id();

        abort_if($file->tenant_id !== $tenantId, 403);
    }
}
