<?php

namespace Modules\Documents\Http\Controllers;

use Modules\Documents\Support\TenantResolver;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\DocumentFolder;

class FoldersController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = TenantResolver::id();

        $currentFolderId = $request->get('folder_id');

        $rootFolders = DocumentFolder::where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $currentFolder = $currentFolderId
            ? DocumentFolder::where('tenant_id', $tenantId)->findOrFail($currentFolderId)
            : null;

        $breadcrumbs = [];
        $tmp = $currentFolder;
        while ($tmp) {
            $breadcrumbs[] = $tmp;
            $tmp = $tmp->parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);

        return view('documents::folders.index', compact(
            'rootFolders',
            'currentFolder',
            'breadcrumbs'
        ));
    }

    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');

        return view('documents::folders.create', compact('parentId'));
    }

    public function store(Request $request)
    {
        $tenantId = TenantResolver::id();

        $data = $request->validate([
            'name'        => 'required|string|max:190',
            'parent_id'   => 'nullable|integer|exists:document_folders,id',
            'description' => 'nullable|string',
        ]);

        $data['tenant_id']  = $tenantId;
        $data['created_by'] = auth()->id();

        $folder = DocumentFolder::create($data);

        return redirect()
            ->route('documents.folders.index', ['folder_id' => $folder->id])
            ->with('success', __('Folder created.'));
    }

    public function edit(DocumentFolder $folder)
    {
        $this->authorizeTenant($folder);

        return view('documents::folders.edit', compact('folder'));
    }

    public function update(Request $request, DocumentFolder $folder)
    {
        $this->authorizeTenant($folder);

        $data = $request->validate([
            'name'        => 'required|string|max:190',
            'description' => 'nullable|string',
        ]);

        $folder->update($data);

        return redirect()
            ->route('documents.folders.index', ['folder_id' => $folder->id])
            ->with('success', __('Folder updated.'));
    }

    public function destroy(DocumentFolder $folder)
    {
        $this->authorizeTenant($folder);

        if ($folder->children()->count() > 0 || $folder->files()->count() > 0) {
            return back()->with('error', __('Cannot delete a folder that contains subfolders or files.'));
        }

        $parentId = $folder->parent_id;

        $folder->delete();

        return redirect()
            ->route('documents.folders.index', ['folder_id' => $parentId])
            ->with('success', __('Folder deleted.'));
    }

    protected function authorizeTenant(DocumentFolder $folder): void
    {
        $tenantId = TenantResolver::id();

        abort_if($folder->tenant_id !== $tenantId, 403);
    }
}
