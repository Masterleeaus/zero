<?php

namespace Modules\FieldItems\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\AccountBaseController;
use App\Traits\IconTrait;
use App\Helper\Reply;
use App\Helper\Files;
use Modules\FieldItems\Entities\Item;
use Modules\FieldItems\Entities\ItemFiles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ItemFileController extends AccountBaseController
{

    use IconTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = __('icon-people');
        $this->pageTitle = 'app.menu.item';
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {

            $defaultImage = null;

            foreach ($request->file as $fileData) {
                $file = new ItemFiles();
                $file->item_id = $request->item_id;

                $filename = Files::uploadLocalOrS3($fileData, ItemFiles::FILE_PATH);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();

                if ($fileData->getClientOriginalName() == $request->default_image) {
                    $defaultImage = $filename;
                }

            }

            $item = Item::findOrFail($request->item_id);
            $item->default_image = $defaultImage;
            $item->save();

        }

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function updateImages(Request $request)
    {
        $defaultImage = null;

        if ($request->hasFile('file')) {
            foreach ($request->file as $file) {
                $itemFile = new ItemFiles();
                $itemFile->item_id = $request->item_id;
                $filename = Files::uploadLocalOrS3($file, 'items');
                $itemFile->filename = $file->getClientOriginalName();
                $itemFile->hashname = $filename;
                $itemFile->size = $file->getSize();
                $itemFile->save();

                if ($itemFile->filename == $request->default_image) {
                    $defaultImage = $filename;
                }

            }
        }

        $item = Item::findOrFail($request->item_id);
        $item->default_image = $defaultImage ?: $request->default_image;
        $item->save();

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request, $id)
    {
        ItemFiles::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function download($id)
    {
        $file = ItemFiles::findOrFail($id);

        return download_local_s3($file, ItemFiles::FILE_PATH . '/' . $file->hashname);
    }
}
