<?php

namespace Modules\TrWorkPermits\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use Modules\TrWorkPermits\Entities\WorkPermitsFile;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;

class WorkPermitsFileController extends AccountBaseController
{
      /**
     * @param Request $request
     * @return mixed|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(Request $request)
    {

        if ($request->hasFile('file')) {
            $this->storeFiles($request);
            $this->files = WorkPermitsFile::where('wp_id', $request->wp_id)->orderBy('id', 'desc')->get();
            $view        = view('projects.files.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'view' => $view]);
        }
    }

    public function storeMultiple(Request $request)
    {
        if ($request->hasFile('file')) {
            $this->storeFiles($request);
        }
    }

    private function storeFiles($request)
    {
        foreach ($request->file as $fileData) {

            $file           = new WorkPermitsFile();
            $file->wp_id    = $request->wp_id;
            $filename       = Files::uploadLocalOrS3($fileData, WorkPermitsFile::FILE_PATH . '/' . $request->wp_id);
            $file->user_id  = $this->user->id;
            $file->filename = $fileData->getClientOriginalName();
            $file->hashname = $filename;
            $file->size     = $fileData->getSize();
            $file->save();
        }
    }

    public function destroy(Request $request, $id)
    {
        $file = WorkPermitsFile::findOrFail($id);

        Files::deleteFile($file->hashname, WorkPermitsFile::FILE_PATH . '/' . $file->wp_id);
        WorkPermitsFile::destroy($id);

        $this->files = WorkPermitsFile::where('wp_id', $file->wp_id)->orderBy('id', 'desc')->get();

        $view = view('projects.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    public function download($id)
    {
        $file                 = WorkPermitsFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $this->viewPermission = user()->permission('view_project_files');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, WorkPermitsFile::FILE_PATH . '/' . $file->wp_id . '/' . $file->hashname);
    }
}
