<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Modules\Hrm\Entities\Branch;
use Modules\Hrm\Entities\Department;
use Modules\Hrm\Entities\Designation;
use Modules\Hrm\Entities\DocumentType;
use Modules\Hrm\Entities\Employee;
use Modules\Hrm\Entities\EmployeeDocument;
use Modules\Hrm\Entities\PayslipType;
use Modules\Recruitment\Entities\CustomQuestion;
use Modules\Recruitment\Entities\InterviewSchedule;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\JobApplication;
use Modules\Recruitment\Entities\JobApplicationNote;
use Modules\Recruitment\Entities\JobOnBoard;
use Modules\Recruitment\Entities\JobStage;
use Modules\Recruitment\Entities\OfferLetter;
use Modules\Recruitment\Events\ConvertToEmployee;
use Modules\Recruitment\Events\CreateJobApplication;
use Modules\Recruitment\Events\CreateJobApplicationNote;
use Modules\Recruitment\Events\CreateJobApplicationRating;
use Modules\Recruitment\Events\CreateJobApplicationSkill;
use Modules\Recruitment\Events\CreateJobApplicationStageChange;
use Modules\Recruitment\Events\CreateJobBoard;
use Modules\Recruitment\Events\DestroyJobApplication;
use Modules\Recruitment\Events\DestroyJobApplicationNote;
use Modules\Recruitment\Events\DestroyJobBoard;
use Modules\Recruitment\Events\JobApplicationArchive;
use Modules\Recruitment\Events\JobApplicationChangeOrder;
use Modules\Recruitment\Events\UpdateJobBoard;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('jobapplication manage')) {
            $stages = JobStage::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->orderBy('order', 'asc')->get();

            $jobs = Job::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('title', 'id');
            $jobs->prepend('All', '');

            if (isset($request->start_date) && !empty($request->start_date)) {

                $filter['start_date'] = $request->start_date;
            } else {

                $filter['start_date'] = date("Y-m-d", strtotime("-1 month"));
            }

            if (isset($request->end_date) && !empty($request->end_date)) {

                $filter['end_date'] = $request->end_date;
            } else {

                $filter['end_date'] = date("Y-m-d H:i:s", strtotime("+1 hours"));
            }

            if (isset($request->job) && !empty($request->job)) {

                $filter['job'] = $request->job;
            } else {
                $filter['job'] = '';
            }

            return view('recruitment::jobApplication.index', compact('stages', 'jobs', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('jobapplication create')) {
            $jobs = Job::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('title', 'id');
            $jobs->prepend('--', '');
            $questions = CustomQuestion::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();


            return view('recruitment::jobApplication.create', compact('jobs', 'questions'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('jobapplication create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'job' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            if (!empty($request->profile)) {

                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'profile', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    $url = $uplaod['url'];
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            if (!empty($request->resume)) {

                $filenameWithExt1 = $request->file('resume')->getClientOriginalName();
                $filename1        = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1       = $request->file('resume')->getClientOriginalExtension();
                $fileNameToStore1 = $filename1 . '_' . time() . '.' . $extension1;

                $uplaod = upload_file($request, 'resume', $fileNameToStore1, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    $url1 = $uplaod['url'];
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            $stage = JobStage::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->first();
            if (!empty($stage)) {
                $job                  = new JobApplication();
                $job->job             = $request->job;
                $job->name            = $request->name;
                $job->email           = $request->email;
                $job->phone           = $request->phone;
                $job->profile         = !empty($request->profile) ? $url : '';
                $job->resume          = !empty($request->resume) ? $url1 : '';
                $job->cover_letter    = $request->cover_letter;
                $job->dob             = $request->dob;
                $job->gender          = $request->gender;
                $job->country         = $request->country;
                $job->state           = $request->state;
                $job->stage           = $stage->id;
                $job->city            = $request->city;
                $job->custom_question = json_encode($request->question);
                $job->workspace       = getActiveWorkSpace();
                $job->created_by      = creatorId();
                $job->save();

                event(new CreateJobApplication($request, $job));

                return redirect()->back()->with('success', __('Job application successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Please create job stage'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($ids)
    {
        if (Auth::user()->isAbleTo('jobapplication show')) {
            $id             = Crypt::decrypt($ids);
            $jobApplication = JobApplication::find($id);
            $jobOnBoards    = JobOnBoard::where('application', $id)->first();
            $interview      = InterviewSchedule::where('candidate', $id)->first();
            $notes          = JobApplicationNote::where('application_id', $id)->get();

            $stages = JobStage::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::jobApplication.show', compact('jobApplication', 'notes', 'stages', 'jobOnBoards', 'interview'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        return view('recruitment::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(JobApplication $jobApplication)
    {
        if (Auth::user()->isAbleTo('jobapplication delete')) {
            if ($jobApplication->profile != null) {
                delete_file($jobApplication->profile);
            }
            if ($jobApplication->resume != null) {
                delete_file($jobApplication->resume);
            }
            event(new DestroyJobApplication($jobApplication));
            $jobApplication->delete();

            return redirect()->route('job-application.index')->with('success', __('Job application   successfully deleted.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        if (Auth::user()->isAbleTo('jobapplication move')) {
            $post = $request->all();
            foreach ($post['order'] as $key => $item) {
                $application        = JobApplication::where('id', '=', $item)->first();
                $application->order = $key;
                $application->stage = $post['stage_id'];
                $application->save();
            }
            event(new JobApplicationChangeOrder($request, $application));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function addSkill(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('jobapplication add skill')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'skill' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job        = JobApplication::find($id);
            $job->skill = $request->skill;
            $job->save();

            event(new CreateJobApplicationSkill($request, $job));

            return redirect()->back()->with('success', __('Job application skill successfully added.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function addNote(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('jobapplication add note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'note' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $note                 = new JobApplicationNote();
            $note->application_id = $id;
            $note->note           = $request->note;
            $note->note_created   = Auth::user()->id;
            $note->created_by     = Auth::user()->id;
            $note->save();

            event(new CreateJobApplicationNote($request, $note));

            return redirect()->back()->with('success', __('Job application notes successfully added.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function destroyNote($id)
    {
        if (Auth::user()->isAbleTo('jobapplication delete note')) {
            $note = JobApplicationNote::find($id);

            event(new DestroyJobApplicationNote($note));

            $note->delete();

            return redirect()->back()->with('success', __('Job application notes successfully deleted.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function rating(Request $request, $id)
    {
        $jobApplication         = JobApplication::find($id);
        $jobApplication->rating = $request->rating;
        $jobApplication->save();

        event(new CreateJobApplicationRating($request, $jobApplication));
    }

    public function archive($id)
    {
        $jobApplication = JobApplication::find($id);
        if ($jobApplication->is_archive == 0) {
            $jobApplication->is_archive = 1;
            $jobApplication->save();

            event(new JobApplicationArchive($jobApplication));

            return redirect()->route('job.application.candidate')->with('success', __('Job application successfully added to archive.'));
        } else {
            $jobApplication->is_archive = 0;
            $jobApplication->save();

            event(new JobApplicationArchive($jobApplication));

            return redirect()->route('job-application.index')->with('success', __('Job application successfully remove to archive.'));
        }
    }

    public function candidate()
    {
        if (Auth::user()->isAbleTo('jobonboard manage')) {
            $archive_application = JobApplication::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->where('is_archive', 1)->get();

            return view('recruitment::jobApplication.candidate', compact('archive_application'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //    -----------------------Job OnBoard-----------------------------_

    public function jobBoardCreate($id)
    {
        if (Auth::user()->isAbleTo('jobonboard create')) {
            $status          = JobOnBoard::$status;
            $job_type        = JobOnBoard::$job_type;
            $salary_duration = JobOnBoard::$salary_duration;
            $salary_type     = PayslipType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $applications    = InterviewSchedule::select('interview_schedules.*', 'job_applications.name')->join('job_applications', 'interview_schedules.candidate', '=', 'job_applications.id')->where('interview_schedules.workspace', getActiveWorkSpace())->get()->pluck('name', 'candidate');
            $applications->prepend('-', '');

            return view('recruitment::jobApplication.onboardCreate', compact('id', 'status', 'applications', 'salary_type', 'job_type', 'salary_duration'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function jobOnBoard()
    {
        if (Auth::user()->isAbleTo('jobonboard manage')) {
            $jobOnBoards = JobOnBoard::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::jobApplication.onboard', compact('jobOnBoards'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardStore(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('jobonboard create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'joining_date' => 'required',
                    'status' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $id = ($id == 0) ? $request->application : $id;

            $jobBoard                        = new JobOnBoard();
            $jobBoard->application           = $id;
            $jobBoard->joining_date          = $request->joining_date;
            $jobBoard->job_type              = $request->job_type;
            $jobBoard->days_of_week          = $request->days_of_week;
            $jobBoard->salary                = $request->salary;
            $jobBoard->salary_type           = $request->salary_type;
            $jobBoard->salary_duration       = $request->salary_duration;
            $jobBoard->status                = $request->status;
            $jobBoard->workspace             = getActiveWorkSpace();
            $jobBoard->created_by            = \Auth::user()->id;
            $jobBoard->save();

            event(new CreateJobBoard($request, $jobBoard));

            $interview = InterviewSchedule::where('candidate', $id)->first();
            if (!empty($interview)) {
                $interview->delete();
            }

            return redirect()->back()->with('success', __('Candidate succefully added in job board.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardUpdate(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('jobonboard edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'joining_date' => 'required',
                    'status' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $jobBoard                        = JobOnBoard::find($id);
            $jobBoard->joining_date          = $request->joining_date;
            $jobBoard->job_type              = $request->job_type;
            $jobBoard->days_of_week          = $request->days_of_week;
            $jobBoard->salary                = $request->salary;
            $jobBoard->salary_type           = $request->salary_type;
            $jobBoard->salary_duration       = $request->salary_duration;
            $jobBoard->status                = $request->status;
            $jobBoard->save();

            event(new UpdateJobBoard($request, $jobBoard));

            return redirect()->back()->with('success', __('Job board Candidate succefully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardEdit($id)
    {
        if (Auth::user()->isAbleTo('jobonboard edit')) {
            $jobOnBoard        = JobOnBoard::find($id);
            $status            = JobOnBoard::$status;
            $job_type          = JobOnBoard::$job_type;
            $salary_duration   = JobOnBoard::$salary_duration;
            $salary_type       = PayslipType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('recruitment::jobApplication.onboardEdit', compact('jobOnBoard', 'status', 'job_type', 'salary_duration', 'salary_type'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function jobBoardDelete($id)
    {
        if (Auth::user()->isAbleTo('jobonboard delete')) {
            $jobBoard = JobOnBoard::find($id);

            event(new DestroyJobBoard($jobBoard));

            $jobBoard->delete();

            return redirect()->route('job.on.board')->with('success', __('Job onBoard successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardConvert($id)
    {
        if (Auth::user()->isAbleTo('jobonboard convert')) {
            $jobOnBoard       = JobOnBoard::find($id);
            $user             = User::where('id', $jobOnBoard->convert_to_employee)->first();
            $documents        = DocumentType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $branches         = Branch::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $departments      = Department::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $designations     = Designation::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $employees        = Employee::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $employeesId      = Employee::employeeIdFormat($this->employeeNumber());
            $roles             = Role::where('created_by', creatorId())->whereNotIn('name', \Auth::user()->not_emp_type)->get()->pluck('name', 'id');

            return view('recruitment::jobApplication.convert', compact('jobOnBoard', 'employees', 'employeesId', 'departments', 'designations', 'documents', 'branches', 'roles', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardConvertData(Request $request, $id)
    {
        if (Auth::user()->type != 'super admin') {
            $canUse =  PlanCheck('User', \Auth::user()->id);
            if ($canUse == false) {
                return redirect()->back()->with('error', 'You have maxed out the total number of User allowed on your current plan');
            }
        }
        $roles            = Role::where('created_by', creatorId())->where('id', $request->roles)->first();
        $jobOnBoard       = JobOnBoard::where('id', $id)->first();
        if (Auth::user()->isAbleTo('jobonboard convert')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'roles' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'address' => 'required',
                    'email' => 'required|unique:users',
                    'password' => 'required',
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'designation_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $user = User::create(
                [
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'email_verified_at' => date('Y-m-d h:i:s'),
                    'type' => $roles->name,
                    'lang' => 'en',
                    'workspace_id' => getActiveWorkSpace(),
                    'active_workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ]
            );
            $user->addRole($roles);
            if (!empty($request->document) && !is_null($request->document)) {
                $document_implode = implode(',', array_keys($request->document));
            } else {
                $document_implode = null;
            }
            $employee = Employee::create(
                [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'dob' => $request['dob'],
                    'gender' => $request['gender'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'email' => $user->email,
                    'employee_id' => $this->employeeNumber(),
                    'branch_id' => $request['branch_id'],
                    'department_id' => $request['department_id'],
                    'designation_id' => $request['designation_id'],
                    'company_doj' => $request['company_doj'],
                    'documents' => $document_implode,
                    'account_holder_name' => $request['account_holder_name'],
                    'account_number' => $request['account_number'],
                    'bank_name' => $request['bank_name'],
                    'bank_identifier_code' => $request['bank_identifier_code'],
                    'branch_location' => $request['branch_location'],
                    'tax_payer_id' => $request['tax_payer_id'],
                    'workspace' => $user->workspace_id,
                    'created_by' => $user->created_by,
                ]
            );

            if (!empty($employee)) {
                $JobOnBoard                      = JobOnBoard::find($id);
                $JobOnBoard->convert_to_employee = $user->id;
                $JobOnBoard->save();
            }
            if ($request->hasFile('document')) {
                foreach ($request->document as $key => $document) {

                    $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('document')[$key]->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = multi_upload_file($document, 'document', $fileNameToStore, 'emp_document');
                    if ($uplaod['flag'] == 1) {
                        $url = $uplaod['url'];
                    } else {
                        return redirect()->back()->with('+error', $uplaod['msg']);
                    }
                    $employee_document = EmployeeDocument::create(
                        [
                            'employee_id' => $employee['employee_id'],
                            'document_id' => $key,
                            'document_value' => !empty($url) ? $url : '',
                            'workspace' => getActiveWorkSpace(),
                            'created_by' => creatorId(),
                        ]
                    );
                    $employee_document->save();
                }
            }
            $company_settings = getCompanyAllSetting();
            if (!empty($company_settings['Create User']) && $company_settings['Create User']  == true) {
                $User        = User::where('id', $user->id)->where('workspace_id', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'email' => $User->email,
                    'password' => $request['password'],
                ];
                $resp = EmailTemplate::sendEmailTemplate('New User', [$User->email], $uArr);
                return redirect()->back()->with('success', __('Application successfully converted to employee.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            event(new ConvertToEmployee($request, $employee));

            return redirect()->back()->with('success', __('Application successfully converted to employee.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function employeeNumber()
    {
        $latest = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->employee_id + 1;
    }

    public function getByJob(Request $request)
    {
        $job                  = Job::find($request->id);
        $job->applicant       = !empty($job->applicant) ? explode(',', $job->applicant) : '';
        $job->visibility      = !empty($job->visibility) ? explode(',', $job->visibility) : '';
        $job->custom_question = !empty($job->custom_question) ? explode(',', $job->custom_question) : '';


        return json_encode($job);
    }

    public function stageChange(Request $request)
    {
        $application        = JobApplication::where('id', '=', $request->schedule_id)->first();
        $application->stage = $request->stage;
        $application->save();

        event(new CreateJobApplicationStageChange($request, $application));

        return response()->json(
            [
                'success' => __('This candidate stage successfully changed.'),
            ],
            200
        );
    }

    public function list(Request $request)
    {
        if (Auth::user()->isAbleTo('jobapplication manage')) {
            $applications = JobApplication::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $stages = JobStage::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->orderBy('order', 'asc')->get();

            $jobs = Job::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('title', 'id');
            $jobs->prepend('All', '');

            if (isset($request->start_date) && !empty($request->start_date)) {

                $filter['start_date'] = $request->start_date;
            } else {

                $filter['start_date'] = date("Y-m-d", strtotime("-1 month"));
            }

            if (isset($request->end_date) && !empty($request->end_date)) {

                $filter['end_date'] = $request->end_date;
            } else {

                $filter['end_date'] = date("Y-m-d H:i:s", strtotime("+1 hours"));
            }

            if (isset($request->job) && !empty($request->job)) {

                $filter['job'] = $request->job;
            } else {
                $filter['job'] = '';
            }
            return view('recruitment::jobApplication.list', compact('applications', 'filter', 'jobs', 'stages'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function grid()
    {
        if (Auth::user()->isAbleTo('jobonboard manage')) {
            $jobOnBoards = JobOnBoard::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::jobApplication.grid', compact('jobOnBoards'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function offerletterupdate($lang, Request $request)
    {
        $user = OfferLetter::updateOrCreate(['lang' =>   $lang, 'created_by' =>  \Auth::user()->id], ['content' => $request->offer_content, 'workspace' => getActiveWorkSpace()]);

        return redirect()->back()->with('success', __('Offer Letter successfully saved.'));
    }

    public function offerletterPdf($id)
    {
        $users = Auth::user();
        $currantLang = getActiveLanguage();
        $Offerletter = OfferLetter::where(['lang' =>   $currantLang, 'created_by' =>  creatorId(), 'workspace' => getActiveWorkSpace()])->first();

        $job = JobApplication::find($id);
        $Onboard = JobOnBoard::find($id);
        $name = JobApplication::find($Onboard->application);
        $job_title = job::find($name->job);
        $salary = PayslipType::find($Onboard->salary_type);


        $obj = [
            'applicant_name' => $name->name,
            'app_name' => env('APP_NAME'),
            'job_title' => $job_title->title,
            'job_type' => !empty($Onboard->job_type) ? $Onboard->job_type : '',
            'start_date' => $Onboard->joining_date,
            'workplace_location' => !empty($job->jobs->branches->name) ? $job->jobs->branches->name : '',
            'days_of_week' => !empty($Onboard->days_of_week) ? $Onboard->days_of_week : '',
            'salary' => !empty($Onboard->salary) ? $Onboard->salary : '',
            'salary_type' => !empty($salary->name) ? $salary->name : '',
            'salary_duration' => !empty($Onboard->salary_duration) ? $Onboard->salary_duration : '',
            'offer_expiration_date' => !empty($Onboard->joining_date) ? $Onboard->joining_date : '',

        ];
        $Offerletter->content = OfferLetter::replaceVariable($Offerletter->content, $obj);
        return view('recruitment::jobApplication.template.offerletterpdf', compact('Offerletter', 'name'));
    }

    public function offerletterDoc($id)
    {
        $users = Auth::user();
        $currantLang = getActiveLanguage();
        $Offerletter = OfferLetter::where(['lang' =>   $currantLang, 'created_by' =>   creatorId(), 'workspace' => getActiveWorkSpace()])->first();
        $job = JobApplication::find($id);
        $Onboard = JobOnBoard::find($id);
        $name = JobApplication::find($Onboard->application);
        $job_title = job::find($name->job);
        $salary = PayslipType::find($Onboard->salary_type);
        $obj = [
            'applicant_name' => $name->name,
            'app_name' => env('APP_NAME'),
            'job_title' => $job_title->title,
            'job_type' => !empty($Onboard->job_type) ? $Onboard->job_type : '',
            'start_date' => $Onboard->joining_date,
            'workplace_location' => !empty($job->jobs->branches->name) ? $job->jobs->branches->name : '',
            'days_of_week' => !empty($Onboard->days_of_week) ? $Onboard->days_of_week : '',
            'salary' => !empty($Onboard->salary) ? $Onboard->salary : '',
            'salary_type' => !empty($salary->name) ? $salary->name : '',
            'salary_duration' => !empty($Onboard->salary_duration) ? $Onboard->salary_duration : '',
            'offer_expiration_date' => !empty($Onboard->joining_date) ? $Onboard->joining_date : '',

        ];
        $Offerletter->content = OfferLetter::replaceVariable($Offerletter->content, $obj);
        return view('recruitment::jobApplication.template.offerletterdocx', compact('Offerletter', 'name'));
    }
}
