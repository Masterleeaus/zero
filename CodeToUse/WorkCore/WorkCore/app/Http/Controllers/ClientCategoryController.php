<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\Admin\Customer\StoreClientCategory;
use App\Models\ClientCategory;

class ClientCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View
     */
    public function create()
    {
        $this->categories = ClientCategory::all();
        $this->deletePermission = user()->permission('manage_client_category');

        return view('customers.create_category', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreClientCategory $request
     * @return array
     */
    public function store(StoreClientCategory $request)
    {
        $category = new ClientCategory();
        $category->category_name = strip_tags($request->category_name);
        $category->save();
        $categoryData = ClientCategory::all();

        return Reply::successWithData(__('team chat.recordSaved'), ['data' => $categoryData]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return array|void
     */
    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('manage_client_category');
        abort_403($this->editPermission != 'all');

        $category = ClientCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $categoryData = ClientCategory::all();

        return Reply::successWithData(__('team chat.updateSuccess'), ['data' => $categoryData]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy($id)
    {
        $this->deletePermission = user()->permission('manage_client_category');
        abort_403($this->deletePermission != 'all');

        $category = ClientCategory::findOrFail($id);
        $category->delete();
        $categoryData = ClientCategory::all();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['data' => $categoryData]);
    }

}
