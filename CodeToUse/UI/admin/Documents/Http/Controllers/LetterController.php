<?php

namespace Modules\Documents\Http\Controllers;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Documents\DataTables\LetterDataTable;
use Modules\Documents\Entities\Documents;
use Modules\Documents\Entities\LetterSetting;
use Modules\Documents\Entities\Template;
use Modules\Documents\Enums\LetterVariable;
use Modules\Documents\Http\Requests\Documents\StoreRequest;
use Modules\Documents\Http\Requests\Documents\UpdateRequest;

class LetterController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'documents::app.menu.Documents';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(LetterSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(LetterDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_letter');
        abort_403($this->viewPermission !== 'all');

        $this->pageTitle = 'documents::app.menu.generate';
        return $dataTable->render('documents::documents.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_letter');
        abort_403($this->addPermission !== 'all');

        $this->pageTitle = __('documents::app.addLetter');

        $this->templates = Template::get();
        $this->employees = User::with('employeeDetail')->onlyEmployee()->get();
        $this->Documents = request()->letterId ? Documents::with('user', 'template')->find(request()->letterId) : null;
        $this->employeeLetterVariable = $this->Documents ? $this->employeeLetterVariable($this->Documents) : [];

        if (request()->ajax()) {

            $html = view('documents::documents.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::documents.ajax.create';
        return view('documents::documents.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_letter');
        abort_403($this->addPermission !== 'all');

        $Documents = new Documents();
        $Documents->template_id = $request->template_id;
        $Documents->user_id = $request->user_id;
        $Documents->creator_id = user()->id;
        $Documents->name = $request->user_id ? null : $request->employeeName;
        $Documents->left = $request->left;
        $Documents->right = $request->right;
        $Documents->top = $request->top;
        $Documents->bottom = $request->bottom;
        $Documents->description = $request->description;
        $Documents->save();

        return Reply::redirect(route('documents.generate.index'), __('messages.recordSaved'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_letter');
        abort_403($this->viewPermission !== 'all');

        $this->Documents = Documents::with('user', 'template')->findOrFail($id);
        $this->pageTitle = $this->Documents->employee_name . ' - ' . $this->Documents->template->title;
        $employeeLetterVariable = $this->employeeLetterVariable($this->Documents);
        $description = $this->Documents->description;

        foreach ($employeeLetterVariable as $key => $value) {
            $description = str_replace($key, $value, $description);
        }

        $this->description = $description;

        if (request()->ajax()) {
            $html = view('documents::documents.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::documents.ajax.show';
        return view('documents::documents.show', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_letter');
        abort_403($this->editPermission !== 'all');

        $this->pageTitle = __('documents::app.editLetter');
        $this->Documents = Documents::with('user', 'template')->findOrFail($id);
        $this->employees = [];

        if ($this->Documents->name) {
            $this->employees = User::with('employeeDetail')->onlyEmployee()->get();
        }

        $this->employeeLetterVariable = $this->employeeLetterVariable($this->Documents);

        if (request()->ajax()) {
            $html = view('documents::documents.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'documents::documents.ajax.edit';
        return view('documents::documents.create', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $this->editPermission = user()->permission('edit_letter');
        abort_403($this->editPermission !== 'all');

        $Documents = Documents::findOrFail($id);

        $Documents->user_id = $request->user_id;
        $Documents->creator_id = user()->id;
        $Documents->name = $request->user_id ? null : $request->employeeName;
        $Documents->left = $request->left;
        $Documents->right = $request->right;
        $Documents->top = $request->top;
        $Documents->bottom = $request->bottom;
        $Documents->description = $request->description;
        $Documents->save();

        return Reply::redirect(route('documents.generate.index'), __('messages.updateSuccess'));
    }

    private function employeeLetterVariable($Documents)
    {
        $letterVariable = [];

        if ($Documents->user_id) {
            $letterVariable = LetterVariable::getMappedValues($Documents->user);
        }
        else {
            $letterVariable = [
                '##EMPLOYEE_NAME##' => $Documents->name,
            ];
        }

        return $letterVariable;
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_letter');
        abort_403($deletePermission !== 'all');

        Documents::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function letterTemplate($id)
    {
        $Documents = Template::findOrFail($id);
        return Reply::dataOnly(['status' => 'success', 'Documents' => $Documents]);
    }

    public function letterEmployee($id)
    {
        $employee = User::with('employeeDetail')->onlyEmployee()->findOrFail($id);

        $letterVariable = [];

        if ($employee) {
            $letterVariable = LetterVariable::getMappedValues($employee);
        }

        return Reply::dataOnly(['status' => 'success', 'employeeLetterVariable' => $letterVariable]);
    }

    public function downloadLetterPreviewStore(Request $request)
    {
        $this->viewPermission = user()->permission('view_letter');
        abort_403($this->viewPermission !== 'all');

        session()->put('letterPreview', $request->description);

        return Reply::dataOnly(['status' => 'success', 'url' => route('documents.download.preview')]);
    }

    public function downloadLetterPreview()
    {
        $this->viewPermission = user()->permission('view_letter');
        abort_403($this->viewPermission !== 'all');

        if (!session()->has('letterPreview')) {
            return abort(404);
        }

        $this->Documents = session('letterPreview');
        session()->forget('letterPreview');
        $this->pageTitle = __('documents::app.previewLetter');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('documents::documents.pdf.preview', $this->data);
        return $pdf->download($this->pageTitle . '.pdf');
    }

    public function downloadLetter($id)
    {
        $this->viewPermission = user()->permission('view_letter');
        abort_403($this->viewPermission !== 'all');

        $this->Documents = Documents::with('user', 'template')->findOrFail($id);
        $employeeLetterVariable = $this->employeeLetterVariable($this->Documents);
        $description = $this->Documents->description;

        foreach ($employeeLetterVariable as $key => $value) {
            $description = str_replace($key, $value, $description);
        }

        $this->description = $description;
        $this->pageTitle = $this->Documents->employee_name . ' - ' . $this->Documents->template->title;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('documents::documents.pdf.Documents', $this->data);
        return $pdf->download($this->pageTitle . '.pdf');
    }

}
