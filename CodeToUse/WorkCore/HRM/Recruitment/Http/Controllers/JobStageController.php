<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobStage;
use Modules\Recruitment\Events\CreateJobStage;
use Modules\Recruitment\Events\DestroyJobStage;
use Modules\Recruitment\Events\UpdateJobStage;

class JobStageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('jobstage manage')) {
            $stages = JobStage::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->orderBy('order', 'asc')->get();

            return view('recruitment::jobStage.index', compact('stages'));
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
        if (Auth::user()->isAbleTo('jobstage create')) {
            return view('recruitment::jobStage.create');
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
        if (Auth::user()->isAbleTo('jobstage create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $jobStage             = new JobStage();
            $jobStage->title      = $request->title;
            $jobStage->workspace  = getActiveWorkSpace();
            $jobStage->created_by = creatorId();
            $jobStage->save();

            event(new CreateJobStage($request, $jobStage));

            return redirect()->back()->with('success', __('Job stage  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(JobStage $jobStage)
    {
        return redirect()->back();
        return view('recruitment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(JobStage $jobStage)
    {
        if (Auth::user()->isAbleTo('jobstage edit')) {
            return view('recruitment::jobStage.edit', compact('jobStage'));
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
    public function update(Request $request, JobStage $jobStage)
    {
        if (Auth::user()->isAbleTo('jobstage edit')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $jobStage->title      = $request->title;
            $jobStage->save();

            event(new UpdateJobStage($request, $jobStage));

            return redirect()->back()->with('success', __('Job stage  successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(JobStage $jobStage)
    {
        if (Auth::user()->isAbleTo('jobstage delete')) {
            if ($jobStage->created_by == creatorId() && $jobStage->workspace == getActiveWorkSpace()) {
                event(new DestroyJobStage($jobStage));

                $jobStage->delete();

                return redirect()->back()->with('success', __('Job stage successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        $post = $request->all();
        foreach ($post['order'] as $key => $item) {
            $stage        = JobStage::where('id', '=', $item)->first();
            $stage->order = $key;
            $stage->save();
        }
    }
}
