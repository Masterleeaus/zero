<?php

namespace App\Http\Controllers;

use App\Models\ClientDocument;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\ClientDocs\CreateRequest;
use App\Http\Requests\ClientDocs\UpdateRequest;
use App\Models\User;

class ClientDocController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.clientDocs';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_client_document');
        abort_403(!($this->addPermission == 'all'));
        $this->user = User::findOrFail(user()->id);

        return view('profile-settings.ajax.customer.create', $this->data);
    }

    public function store(CreateRequest $request)
    {
        $fileFormats = ['image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/pdf', 'text/plain'];

        foreach ($request->file as $index => $fFormat) {
            if (!in_array($fFormat->getClientMimeType(), $fileFormats)) {
                return Reply::error(__('team chat.employeeDocsAllowedFormat'));
            }
        }

        $file = new ClientDocument();
        $file->user_id = $request->user_id;

        $filename = Files::uploadLocalOrS3($request->file, ClientDocument::FILE_PATH . '/' . $request->user_id);

        $file->name = $request->name;
        $file->filename = $request->file->getClientOriginalName();
        $file->hashname = $filename;
        $file->size = $request->file->getSize();
        $file->save();

        $this->files = ClientDocument::where('user_id', $request->user_id)->orderByDesc('id')->get();
        $view = view('customers.files.show', $this->data)->render();

        return Reply::successWithData(__('team chat.recordSaved'), ['status' => 'success', 'view' => $view]);
    }

    public function edit($id)
    {
        $this->file = ClientDocument::findOrFail($id);

        $editPermission = user()->permission('edit_client_document');
        abort_403(!($editPermission == 'all'
        || ($editPermission == 'added' && $this->file->added_by == user()->id)
        || ($editPermission == 'owned' && ($this->file->user_id == user()->id && $this->file->added_by != user()->id))
        || ($editPermission == 'both' && ($this->file->added_by == user()->id || $this->file->user_id == user()->id))));

        return view('customers.files.edit', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $file = ClientDocument::findOrFail($id);

        $file->name = $request->name;

        if ($request->file) {
            $filename = Files::uploadLocalOrS3($request->file, ClientDocument::FILE_PATH . '/' . $file->user_id);
            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = $request->file->getSize();
        }

        $file->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function destroy($id)
    {
        $file = ClientDocument::findOrFail($id);
        $deleteDocumentPermission = user()->permission('delete_client_document');
        abort_403(!($deleteDocumentPermission == 'all'
        || ($deleteDocumentPermission == 'added' && $file->added_by == user()->id)
        || ($deleteDocumentPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
        || ($deleteDocumentPermission == 'both' && ($file->added_by == user()->id || $file->user_id == user()->id))));

        Files::deleteFile($file->hashname, ClientDocument::FILE_PATH . '/' . $file->user_id);

        ClientDocument::destroy($id);

        $this->files = ClientDocument::where('user_id', $file->user_id)->orderByDesc('id')->get();

        $view = view('customers.files.show', $this->data)->render();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['view' => $view]);

    }

    public function show($id)
    {
        $file = ClientDocument::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $viewPermission = user()->permission('view_client_document');

        abort_403(!($viewPermission == 'all'
        || ($viewPermission == 'added' && $file->added_by == user()->id)
        || ($viewPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
        || ($viewPermission == 'both' && ($file->added_by == user()->id || $file->user_id == user()->id))));

        $this->filepath = $file->doc_url;
        return view('customers.files.view', $this->data);

    }

    public function download($id)
    {
        $file = ClientDocument::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $viewPermission = user()->permission('view_client_document');

        abort_403(!($viewPermission == 'all'
        || ($viewPermission == 'added' && $file->added_by == user()->id)
        || ($viewPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
        || ($viewPermission == 'both' && ($file->added_by == user()->id || $file->added_by == user()->id))));

        return download_local_s3($file, ClientDocument::FILE_PATH . '/' . $file->user_id . '/' . $file->hashname);
    }

}
