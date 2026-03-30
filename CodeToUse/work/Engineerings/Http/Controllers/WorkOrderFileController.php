<?php

namespace Modules\Engineerings\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use Modules\Engineerings\Entities\WorkOrderFile;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;

class WorkOrderFileController extends AccountBaseController
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
            $this->files = WorkOrderFile::where('workorder_id', $request->workorderID)
                ->orderBy('id', 'desc')
                ->get();
            $view = view('projects.files.show', $this->data)->render();

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
            $file               = new WorkOrderFile();
            $file->workorder_id = $request->workorderID;
            $filename           = Files::uploadLocalOrS3($fileData, WorkOrderFile::FILE_PATH . '/' . $request->workorderID);
            $file->user_id      = $this->user->id;
            $file->filename     = $fileData->getClientOriginalName();
            $file->hashname     = $filename;
            $file->size         = $fileData->getSize();
            $file->save();
        }
    }

    public function destroy(Request $request, $id)
    {
        $file = WorkOrderFile::findOrFail($id);
        Files::deleteFile($file->hashname, WorkOrderFile::FILE_PATH . '/' . $file->workorderID);
        WorkOrderFile::destroy($id);

        $this->files = WorkOrderFile::where('workorder_id', $file->workorderID)
            ->orderBy('id', 'desc')
            ->get();

        $view = view('projects.files.show', $this->data)->render();
        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    public function download($id)
    {
        $file                 = WorkOrderFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $this->viewPermission = user()->permission('view_project_files');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, WorkOrderFile::FILE_PATH . '/' . $file->workorderID . '/' . $file->hashname);
    }
}
