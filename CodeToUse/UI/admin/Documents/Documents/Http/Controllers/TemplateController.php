<?php

namespace Modules\Documents\Http\Controllers;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\Documents\DataTables\TemplateDataTable;
use Modules\Documents\Entities\LetterSetting;
use Modules\Documents\Entities\Template;
use Modules\Documents\Http\Requests\Template\StoreRequest;
use Modules\Documents\Http\Requests\Template\UpdateRequest;

class TemplateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'documents::app.menu.template';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(LetterSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(TemplateDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_template');
        abort_403($this->viewPermission !== 'all');

        return $dataTable->render('documents::template.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_template');
        abort_403($this->addPermission !== 'all');

        $this->pageTitle = __('documents::app.addTemplate');

        if (request()->ajax()) {

            $html = view('documents::template.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::template.ajax.create';
        return view('documents::template.create', $this->data);

    }

    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_template');
        abort_403($this->addPermission !== 'all');

        $Documents = new Template();
        $Documents->title = $request->title;
        $Documents->description = $request->description;
        $Documents->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('documents.template.index')]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_template');
        abort_403($this->editPermission !== 'all');

        $this->pageTitle = __('documents::app.editTemplate');
        $this->Documents = Template::find($id);

        if (request()->ajax()) {

            $html = view('documents::template.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::template.ajax.edit';
        return view('documents::template.create', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $this->editPermission = user()->permission('edit_template');
        abort_403($this->editPermission !== 'all');

        $Documents = Template::find($id);
        $Documents->title = $request->title;
        $Documents->description = $request->description;
        $Documents->save();
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('documents.template.index')]);

    }

    public function show($id)
    {
        $this->viewPermission = user()->permission('view_template');
        abort_403($this->viewPermission !== 'all');

        $this->pageTitle = __('documents::app.showTemplate');

        $this->Documents = Template::find($id);

        if (request()->ajax()) {
            $html = view('documents::template.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::template.ajax.show';
        return view('documents::template.show', $this->data);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_template');
        abort_403($deletePermission !== 'all');

        Template::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

}
