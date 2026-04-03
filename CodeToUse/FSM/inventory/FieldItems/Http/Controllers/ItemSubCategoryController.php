<?php

namespace Modules\FieldItems\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FieldItems\Entities\ItemCategory;
use Modules\FieldItems\Entities\ItemSubCategory;
use Modules\FieldItems\Http\Requests\Item\StoreItemSubCategory;
use App\Http\Controllers\AccountBaseController;
use App\Helper\Reply;

class ItemSubCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $this->categoryID = $request->catID;
        $this->subcategories = ItemSubCategory::all();
        $this->categories = ItemCategory::all();

        return view('fielditems::items.sub-category.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreItemSubCategory $request)
    {
        $category = new ItemSubCategory();
        $category->category_id = $request->category_id;
        $category->category_name = $request->category_name;
        $category->save();

        $categoryData = ItemCategory::get();
        $subCategoryData = ItemSubCategory::get();
        $category = '';
        $subCategory = '';

        foreach ($subCategoryData as $item) {
            $subCategory .= '<option value='.$item->id.'>'.ucwords($item->category_name).'</option>';
        }

        foreach ($categoryData as $data) {
            $category .= '<option value='.$data->id.'>'.ucwords($data->category_name).'</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $category, 'subCategoryData' => $subCategory]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $category = ItemSubCategory::findOrFail($id);
        $category->category_id = $request->category_id ? $request->category_id : $category->category_id;
        $category->category_name = $request->category_name ? strip_tags($request->category_name) : $category->category_name;
        $category->save();

        $subCategoryOptions = $this->subCategoryDropdown($category->category_id);
        $categoryOptions = $this->categoryDropdown($category->id);

        return Reply::successWithData(__('messages.updateSuccess'), ['sub_categories' => $subCategoryOptions, 'categories' => $categoryOptions]);
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        ItemSubCategory::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function categoryDropdown($selectId = null)
    {
        /* Category Dropdown */
        $categoryData = ItemCategory::get();
        $categoryOptions = '<option value="">--</option>';

        foreach ($categoryData as $item) {
            $selected = '';

            if (!is_null($selectId) && $item->id == $selectId) {
                $selected = 'selected';
            }

            $categoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->category_name . ' </option>';
        }

        return $categoryOptions;
    }

    public function subCategoryDropdown($selectId)
    {
        /* Sub-Category Dropdown */
        $subCategoryData = ItemSubCategory::get();
        $subCategoryOptions = '<option value="">--</option>';

        foreach ($subCategoryData as $item) {
            $selected = '';

            if ($item->id == $selectId) {
                $selected = 'selected';
            }

            $subCategoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->category_name . ' </option>';
        }

        return $subCategoryOptions;
    }

    public function getSubCategories($id)
    {
        $sub_categories = ($id == 'null') ? ItemSubCategory::get() : ItemSubCategory::where('category_id', $id)->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $sub_categories]);
    }
}
