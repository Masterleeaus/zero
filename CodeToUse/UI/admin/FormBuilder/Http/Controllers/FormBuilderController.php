<?php

namespace Modules\FormBuilder\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\FormBuilder\Entities\FormBuilder;
use Modules\FormBuilder\Entities\FormField;
use Modules\FormBuilder\Entities\FormFieldResponse;
use Modules\FormBuilder\Entities\FormResponse;
use Modules\FormBuilder\Entities\UserLead;
use Modules\FormBuilder\Events\ConvertIntoLeadSetting;
use Modules\FormBuilder\Events\ViewForm;
use Modules\FormBuilder\Events\CreateForm;
use Modules\FormBuilder\Events\CreateFormField;
use Modules\FormBuilder\Events\DestroyForm;
use Modules\FormBuilder\Events\DestroyFormField;
use Modules\FormBuilder\Events\UpdateForm;
use Modules\FormBuilder\Events\UpdateFormField;

class FormBuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(Auth::user()->isAbleTo('formbuilder manage'))
        {
            $forms = FormBuilder::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get();

            return view('formbuilder::form_builder.index', compact('forms'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('formbuilder::form_builder.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('formbuilder create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('form_builder.index')->with('error', $messages->first());
            }

            $form_builder             = new FormBuilder();
            $form_builder->name       = $request->name;
            $form_builder->code       = uniqid() . time();
            $form_builder->is_active  = $request->is_active;
            $form_builder->created_by = creatorId();
            $form_builder->workspace  = getActiveWorkSpace();
            $form_builder->save();

            event(new CreateForm($request,$form_builder));

            return redirect()->route('form_builder.index')->with('success', __('Form successfully created!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(FormBuilder $formBuilder)
    {
        if(Auth::user()->type == 'company')
        {
            if($formBuilder->created_by == creatorId() && $formBuilder->workspace == getActiveWorkSpace())
            {
                return view('formbuilder::form_builder.show', compact('formBuilder'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(FormBuilder $formBuilder)
    {
        if(AUth::user()->type == 'company')
        {
            if($formBuilder->created_by == creatorId() && $formBuilder->workspace == getActiveWorkSpace())
            {
                return view('formbuilder::form_builder.edit', compact('formBuilder'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, FormBuilder $formBuilder)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            if($formBuilder->created_by == creatorId() && $formBuilder->workspace == getActiveWorkSpace())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('form_builder.index')->with('error', $messages->first());
                }

                $formBuilder->name           = $request->name;
                $formBuilder->is_active      = $request->is_active;
                $formBuilder->is_lead_active = 0;
                $formBuilder->save();

                event(new UpdateForm($request,$formBuilder));

                return redirect()->route('form_builder.index')->with('success', __('Form successfully updated!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(FormBuilder $formBuilder)
    {
        if(Auth::user()->type == 'company')
        {
            if($formBuilder->created_by == creatorId() && $formBuilder->workspace == getActiveWorkSpace())
            {
                FormField::where('form_id', '=', $formBuilder->id)->delete();
                FormFieldResponse::where('form_id', '=', $formBuilder->id)->delete();
                FormResponse::where('form_id', '=', $formBuilder->id)->delete();

                $formBuilder->delete();

                event(new DestroyForm($formBuilder));

                return redirect()->route('form_builder.index')->with('success', __('Form successfully deleted!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function formView($code)
    {
        if(!empty($code))
        {
            try {
                $code = $code;
            } catch (\Throwable $th) {
                return redirect('login');
            }
            $form = FormBuilder::where('code', 'LIKE', $code)->first();
            $company_id = $form->created_by;
            $workspace_id = $form->workspace;
            if(!empty($form))
            {
                if($form->is_active == 1)
                {
                    $objFields = $form->form_field;

                    return view('formbuilder::form_builder.form_view', compact('objFields', 'code', 'form','company_id','workspace_id'));
                }
                else
                {
                    return view('formbuilder::form_builder.form_view', compact('code', 'form','company_id','workspace_id'));
                }
            }
            else
            {
                return redirect()->route('login')->with('error', __('Form not found please contact to admin.'));
            }
        }
        else
        {
            return redirect()->route('login')->with('error', __('Permission Denied.'));
        }
    }

    public function viewResponse($form_id)
    {
        if(Auth::user()->type == 'company')
        {
            $form = FormBuilder::find($form_id);
            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                return view('formbuilder::form_builder.response', compact('form'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied . ')], 401);
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function responseDetail($response_id)
    {
        if(Auth::user()->type == 'company')
        {
            $formResponse = FormResponse::find($response_id);
            $form         = FormBuilder::find($formResponse->form_id);
            if($form->created_by == creatorId())
            {
                $response = json_decode($formResponse->response, true);

                return view('formbuilder::form_builder.response_detail', compact('response'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied . ')], 401);
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fieldCreate($id)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $formbuilder = FormBuilder::find($id);
            if($formbuilder->created_by == creatorId() && $formbuilder->workspace == getActiveWorkSpace())
            {
                $types = FormBuilder::$fieldTypes;

                return view('formbuilder::form_builder.field_create', compact('types', 'formbuilder'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fieldStore($id, Request $request)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $formbuilder = FormBuilder::find($id);
            if($formbuilder->created_by == creatorId() && $formbuilder->workspace == getActiveWorkSpace())
            {
                $names = $request->name;
                $types = $request->type;

                foreach($names as $key => $value)
                {
                    if(!empty($value))
                    {
                        // create form field
                        FormField::create(
                            [
                                'form_id' => $formbuilder->id,
                                'name' => $value,
                                'type' => $types[$key],
                                'created_by' => creatorId(),
                                'workspace'=> getActiveWorkSpace(),
                            ]
                        );
                    }
                }

                event(new CreateFormField($request,$formbuilder));

                return redirect()->back()->with('success', __('Field successfully created!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fieldEdit($id, $field_id)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $form = FormBuilder::find($id);
            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                $form_field = FormField::find($field_id);

                if(!empty($form_field))
                {
                    $types = FormBuilder::$fieldTypes;

                    return view('formbuilder::form_builder.field_edit', compact('form_field', 'types', 'form'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Field not found.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fieldUpdate($id, $field_id, Request $request)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $form = FormBuilder::find($id);
            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $field = FormField::find($field_id);
                $field->update(
                    [
                        'name' => $request->name,
                        'type' => $request->type,
                    ]
                );

                event(new UpdateFormField($request,$form));

                return redirect()->back()->with('success', __('Form successfully updated!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fieldDestroy($id, $field_id)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $form = FormBuilder::find($id);
            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                $form_field_response = FormFieldResponse::orWhere('subject_id', '=', $field_id)->orWhere('name_id', '=', $field_id)->orWhere('email_id', '=', $field_id)->first();

                if(!empty($form_field_response))
                {
                    return redirect()->back()->with('error', __('Please remove this field from Convert Lead.'));
                }
                else
                {
                    $form_field = FormField::find($field_id);
                    if(!empty($form_field))
                    {
                        $form_field->delete();
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Field not found.'));
                    }

                    event(new DestroyFormField($form));

                    return redirect()->back()->with('success', __('Form successfully deleted!'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function formFieldBind($form_id)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $form = FormBuilder::find($form_id);

            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                $types     = $form->form_field->pluck('name', 'id');
                $formField = FormFieldResponse::where('form_id', '=', $form_id)->first();

                // Get Users
                $users =User::where('workspace_id',getActiveWorkSpace())->emp()->get()->pluck('name', 'id');

                // Pipelines
                $pipelines = \Modules\Lead\Entities\Pipeline::where('created_by', '=', creatorId())->where('workspace_id',getActiveWorkSpace())->get()->pluck('name', 'id');

                return view('formbuilder::form_builder.form_field', compact('form', 'types', 'formField', 'users', 'pipelines'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    // Store convert into lead modal
    public function bindStore(Request $request, $id)
    {
        $usr = Auth::user();
        if($usr->type == 'company')
        {
            $form                 = FormBuilder::find($id);
            $form->is_lead_active = $request->is_lead_active;
            $form->save();

            if($form->created_by == creatorId() && $form->workspace == getActiveWorkSpace())
            {
                if($form->is_lead_active == 1)
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'subject_id' => 'required',
                                           'name_id' => 'required',
                                           'email_id' => 'required',
                                           'user_id' => 'required',
                                           'pipeline_id' => 'required',
                                       ]
                    );

                    if($validator->fails())
                    {
                        $messages = $validator->getMessageBag();

                        // if validation failed then make status 0
                        $form->is_lead_active = 0;
                        $form->save();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    if(!empty($request->form_response_id))
                    {
                        // if record already exists then update it.
                        $field_bind = FormFieldResponse::find($request->form_response_id);
                        $field_bind->update(
                            [
                                'subject_id' => $request->subject_id,
                                'name_id' => $request->name_id,
                                'email_id' => $request->email_id,
                                'user_id' => $request->user_id,
                                'pipeline_id' => $request->pipeline_id,
                                'workspace'=> getActiveWorkSpace(),
                            ]
                        );
                    }
                    else
                    {
                        // Create Field Binding record on form_field_responses tbl
                        FormFieldResponse::create(
                            [
                                'form_id' => $request->form_id,
                                'subject_id' => $request->subject_id,
                                'name_id' => $request->name_id,
                                'email_id' => $request->email_id,
                                'user_id' => $request->user_id,
                                'pipeline_id' => $request->pipeline_id,
                                'workspace'=> getActiveWorkSpace(),
                            ]
                        );
                    }
                }

            event(new ConvertIntoLeadSetting($request,$form));

                return redirect()->back()->with('success', __('Setting saved successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function formViewStore(Request $request)
    {
        // Get form
        $form = FormBuilder::where('code', 'LIKE', $request->code)->first();

        if(!empty($form))
        {
            $arrFieldResp = [];
            foreach($request->field as $key => $value)
            {
                $arrFieldResp[FormField::find($key)->name] = (!empty($value)) ? $value : '-';
            }

            // store response
            FormResponse::create(
                [
                    'form_id' => $form->id,
                    'response' => json_encode($arrFieldResp),
                ]
            );

            // in form convert lead is active then creat lead
            $lead = 0;
            if($form->is_lead_active == 1)
            {
                $objField = $form->fieldResponse;

                // validation
                $email = User::where('email', 'LIKE', $request->field[$objField->email_id])->first();

                if(!empty($email))
                {
                    return redirect()->back()->with('error', __('Email already exist in our record.!'));
                }

                $usr   = User::find($form->created_by);
                $stage = \Modules\Lead\Entities\LeadStage::where('pipeline_id', '=', $objField->pipeline_id)->first();
                if(!empty($stage))
                {
                    $lead              = new \Modules\Lead\Entities\Lead();
                    $lead->name        = $request->field[$objField->name_id];
                    $lead->email       = $request->field[$objField->email_id];
                    $lead->subject     = $request->field[$objField->subject_id];
                    $lead->user_id     = $objField->user_id;
                    $lead->pipeline_id = $objField->pipeline_id;
                    $lead->stage_id    = $stage->id;
                    $lead->created_by  = $usr->id;
                    $lead->date        = date('Y-m-d');
                    $lead->workspace_id = $usr->active_workspace;
                    $lead->save();

                    $usrLeads = [
                        $usr->id,
                        $objField->user_id,
                    ];

                    foreach($usrLeads as $usrLead)
                    {
                        UserLead::create(
                            [
                                'user_id' => $usrLead,
                                'lead_id' => $lead->id,
                            ]
                        );
                    }

                }
            }
            event(new ViewForm($request,$lead,$form));

            return redirect()->back()->with('success', __('Data submit successfully!'));
        }
        else
        {
            return redirect()->route('login')->with('error', __('Something went wrong.'));
        }

    }
}
