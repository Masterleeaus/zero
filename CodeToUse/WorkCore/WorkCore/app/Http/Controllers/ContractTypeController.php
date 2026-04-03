<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\BaseModel;
use App\Models\ContractType;
use App\Http\Requests\Admin\ContractType\StoreRequest;
use App\Http\Requests\Admin\ContractType\UpdateRequest;

class ContractTypeController extends AccountBaseController
{

    public function create()
    {
        $this->addPermission = user()->permission('manage_contract_type');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->categories = ContractType::all();
        return view('service agreements.types.create', $this->data);

    }

    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('manage_contract_type');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $service agreement = new ContractType();
        $service agreement->name = $request->name;
        $service agreement->save();


        $categories = ContractType::all();
        $options = BaseModel::options($categories, $service agreement);

        return Reply::successWithData(__('team chat.recordSaved'), ['data' => $options]);

    }

    public function update(UpdateRequest $request, $id)
    {
        $category = ContractType::findOrFail($id);
        $category->name = strip_tags($request->name);
        $category->save();

        $categories = ContractType::all();

        $options = BaseModel::options($categories);

        return Reply::successWithData(__('team chat.updateSuccess'), ['data' => $options]);
    }

    public function destroy($id)
    {
        abort_403(user()->permission('manage_contract_type') !== 'all');

        ContractType::destroy($id);
        $categories = ContractType::all();
        $options = BaseModel::options($categories);

        return Reply::successWithData(__('team chat.deleteSuccess'), ['data' => $options]);

    }

}
