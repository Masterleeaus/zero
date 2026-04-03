<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Entities\DocumentRequest;
use Modules\Documents\Entities\DocumentRequestUpload;

class PublicDocumentRequestController extends Controller
{
    public function show(string $token)
    {
        $req = DocumentRequest::withoutGlobalScopes()->where('token', $token)->firstOrFail();

        return view('documents::requests.public_upload', ['req' => $req]);
    }

    public function upload(Request $request, string $token)
    {
        $req = DocumentRequest::withoutGlobalScopes()->where('token', $token)->firstOrFail();

        if (in_array($req->status, ['cancelled'])) {
            abort(410);
        }

        $request->validate([
            'file' => 'required|file|max:25600', // 25MB
        ]);

        $file = $request->file('file');

        $path = $file->store('documents/requests/'.$req->tenant_id.'/'.$req->id, ['disk' => config('filesystems.default')]);

        DocumentRequestUpload::withoutGlobalScopes()->create([
            'tenant_id' => $req->tenant_id,
            'request_id' => $req->id,
            'document_file_id' => null,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType(),
            'ip' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 255),
        ]);

        $req->forceFill(['status' => 'received', 'received_at' => now()])->save();

        return back()->with('success', __('File uploaded. Thank you.'));
    }
}
