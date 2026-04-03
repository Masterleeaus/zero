<?php

namespace Modules\FieldItems\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FieldItems\Entities\ItemCategory;
use Modules\FieldItems\Http\Requests\Item\StoreItemCategory;
use App\Http\Controllers\AccountBaseController;
use App\Models\BaseModel;
use App\Helper\Reply;

class ItemCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $this->categories = ItemCategory::all();
        return view('fielditems::items.category.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreItemCategory $request)
    {
        $category = new ItemCategory();
        $category->category_name = $request->category_name;
        $category->save();

        $categories = ItemCategory::get();
        $options = BaseModel::options($categories, $category, 'category_name');

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreItemCategory $request, $id)
    {
        $category = ItemCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $categories = ItemCategory::get();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        ItemCategory::destroy($id);
        $categoryData = ItemCategory::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }
}
