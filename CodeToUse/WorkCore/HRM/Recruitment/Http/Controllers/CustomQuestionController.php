<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\CustomQuestion;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\JobApplication;
use Modules\Recruitment\Events\CreateCustomQuestion;
use Modules\Recruitment\Events\DestroyCustomQuestion;
use Modules\Recruitment\Events\UpdateCustomQuestion;

class CustomQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('custom question manage')) {
            $questions = CustomQuestion::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::customQuestion.index', compact('questions'));
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
        if (Auth::user()->isAbleTo('custom question create')) {
            $is_required = CustomQuestion::$is_required;

            return view('recruitment::customQuestion.create', compact('is_required'));
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
        if (Auth::user()->isAbleTo('custom question create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'question' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $question              = new CustomQuestion();
            $question->question    = $request->question;
            $question->is_required = $request->is_required;
            $question->workspace   = getActiveWorkSpace();
            $question->created_by  = creatorId();
            $question->save();

            event(new CreateCustomQuestion($request, $question));

            return redirect()->back()->with('success', __('Question successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(CustomQuestion $customQuestion)
    {
        return redirect()->back();
        return view('recruitment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(CustomQuestion $customQuestion)
    {
        if (Auth::user()->isAbleTo('custom question edit')) {
            $is_required = CustomQuestion::$is_required;
            return view('recruitment::customQuestion.edit', compact('customQuestion', 'is_required'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, CustomQuestion $customQuestion)
    {
        if (Auth::user()->isAbleTo('custom question edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'question' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $customQuestion->question    = $request->question;
            $customQuestion->is_required = $request->is_required;
            $customQuestion->save();

            event(new UpdateCustomQuestion($request, $customQuestion));

            return redirect()->back()->with('success', __('Question successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(CustomQuestion $customQuestion)
    {
        if (Auth::user()->isAbleTo('custom question delete')) {
            $jobs = Job::whereRaw("FIND_IN_SET($customQuestion->id, custom_question)")->get();
            foreach ($jobs as $job) {
                $jobes = $job->custom_question;
                $abc = explode(',', $jobes);
                unset($abc[array_search($customQuestion->id, $abc)]);

                $abc = implode(',', $abc);

                $job->custom_question = $abc;
                $job->save();

                $job_id = $job->id;

                $questions = JobApplication::where('job', $job_id)->get();

                foreach ($questions as $question) {
                    $xyz = json_decode($question->custom_question, true);

                    unset($xyz[$customQuestion->question]);
                    $xyz = json_encode($xyz);
                    $question->custom_question = $xyz;
                    $question->save();
                }
            }

            event(new DestroyCustomQuestion($customQuestion));

            $customQuestion->delete();


            return redirect()->back()->with('success', __('Question successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
