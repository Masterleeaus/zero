<?php

namespace Modules\Engineerings\Http\Controllers;

use Modules\Engineerings\Entities\ServicesCategory;
use Modules\Items\Http\Requests\Item\StoreItemCategory;
use App\Http\Controllers\AccountBaseController;
use App\Models\BaseModel;
use App\Helper\Reply;

class ServicesCategoryController extends AccountBaseController
{

    public function create()
    {
        $this->categories = ServicesCategory::all();
        return view('engineerings::services.category.create', $this->data);
    }

    public function store(StoreItemCategory $request)
    {
        $category       = new ServicesCategory();
        $category->name = $request->category_name;
        $category->save();

        $categories = ServicesCategory::get();
        $options    = BaseModel::options($categories, $category, 'name');

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    public function update(StoreItemCategory $request, $id)
    {
        $category       = ServicesCategory::findOrFail($id);
        $category->name = strip_tags($request->category_name);
        $category->save();

        $categories = ServicesCategory::get();
        $options    = BaseModel::options($categories, null, 'name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    public function destroy($id)
    {
        ServicesCategory::destroy($id);
        $categoryData = ServicesCategory::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }
}
