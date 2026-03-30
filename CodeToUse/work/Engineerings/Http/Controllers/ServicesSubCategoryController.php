<?php

namespace Modules\Engineerings\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Engineerings\Entities\ServicesCategory;
use Modules\Engineerings\Entities\ServicesSubCategory;
use Modules\Items\Http\Requests\Item\StoreItemSubCategory;
use App\Http\Controllers\AccountBaseController;
use App\Helper\Reply;

class ServicesSubCategoryController extends AccountBaseController
{

    public function create(Request $request)
    {
        $this->categoryID = $request->catID;
        $this->subcategories = ServicesSubCategory::all();
        $this->categories = ServicesCategory::all();

        return view('engineerings::services.sub-category.create', $this->data);
    }


    public function store(StoreItemSubCategory $request)
    {
        $category = new ServicesSubCategory();
        $category->category_id = $request->category_id;
        $category->name = $request->category_name;
        $category->save();

        $categoryData = ServicesCategory::get();
        $subCategoryData = ServicesSubCategory::get();
        $category = '';
        $subCategory = '';

        foreach ($subCategoryData as $item) {
            $subCategory .= '<option value=' . $item->id . '>' . ucwords($item->name) . '</option>';
        }

        foreach ($categoryData as $data) {
            $category .= '<option value=' . $data->id . '>' . ucwords($data->name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $category, 'subCategoryData' => $subCategory]);
    }

    public function update(Request $request, $id)
    {
        $category = ServicesSubCategory::findOrFail($id);
        $category->category_id = $request->category_id ? $request->category_id : $category->category_id;
        $category->name = $request->category_name ? strip_tags($request->category_name) : $category->category_name;
        $category->save();

        $subCategoryOptions = $this->subCategoryDropdown($category->category_id);
        $categoryOptions = $this->categoryDropdown($category->id);

        return Reply::successWithData(__('messages.updateSuccess'), ['sub_categories' => $subCategoryOptions, 'categories' => $categoryOptions]);
    }


    public function destroy($id)
    {
        ServicesSubCategory::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function categoryDropdown($selectId = null)
    {
        /* Category Dropdown */
        $categoryData = ServicesCategory::get();
        $categoryOptions = '<option value="">--</option>';

        foreach ($categoryData as $item) {
            $selected = '';

            if (!is_null($selectId) && $item->id == $selectId) {
                $selected = 'selected';
            }

            $categoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->name . ' </option>';
        }

        return $categoryOptions;
    }

    public function subCategoryDropdown($selectId)
    {
        /* Sub-Category Dropdown */
        $subCategoryData = ServicesSubCategory::get();
        $subCategoryOptions = '<option value="">--</option>';

        foreach ($subCategoryData as $item) {
            $selected = '';

            if ($item->id == $selectId) {
                $selected = 'selected';
            }

            $subCategoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->name . ' </option>';
        }

        return $subCategoryOptions;
    }

    public function getSubCategories($id)
    {
        $sub_categories = ($id == 'null') ? ServicesSubCategory::get() : ServicesSubCategory::where('category_id', $id)->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $sub_categories]);
    }
}
