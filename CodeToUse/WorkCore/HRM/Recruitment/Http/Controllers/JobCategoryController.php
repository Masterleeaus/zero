<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\JobCategory;
use Modules\Recruitment\Events\CreateJobCategory;
use Modules\Recruitment\Events\DestroyJobCategory;
use Modules\Recruitment\Events\UpdateJobCategory;

class JobCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('jobcategory manage')) {
            $categories = JobCategory::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::jobCategory.index', compact('categories'));
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
        if (Auth::user()->isAbleTo('jobcategory create')) {
            return view('recruitment::jobCategory.create');
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
        if (Auth::user()->isAbleTo('jobcategory create')) {

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

            $jobCategory             = new JobCategory();
            $jobCategory->title      = $request->title;
            $jobCategory->workspace  = getActiveWorkSpace();
            $jobCategory->created_by = creatorId();
            $jobCategory->save();

            event(new CreateJobCategory($request, $jobCategory));

            return redirect()->back()->with('success', __('Job category  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(JobCategory $jobCategory)
    {
        return redirect()->back();
        return view('recruitment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(JobCategory $jobCategory)
    {
        if (Auth::user()->isAbleTo('jobcategory edit')) {
            return view('recruitment::jobCategory.edit', compact('jobCategory'));
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
    public function update(JobCategory $jobCategory, Request $request)
    {
        if (Auth::user()->isAbleTo('jobcategory edit')) {

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

            $jobCategory->title = $request->title;
            $jobCategory->save();

            event(new UpdateJobCategory($request, $jobCategory));

            return redirect()->back()->with('success', __('Job category  successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(JobCategory $jobCategory)
    {
        if (Auth::user()->isAbleTo('jobcategory delete')) {
            if ($jobCategory->created_by == creatorId() && $jobCategory->workspace == getActiveWorkSpace()) {
                $jobs = Job::where('category', $jobCategory->id)->where('workspace', getActiveWorkSpace())->get();
                if (count($jobs) == 0) {
                    event(new DestroyJobCategory($jobCategory));

                    $jobCategory->delete();
                } else {
                    return redirect()->back()->with('error', __('This Job category has Job. Please remove the Job from this Job category.'));
                }
                return redirect()->back()->with('success', __('Job category successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
