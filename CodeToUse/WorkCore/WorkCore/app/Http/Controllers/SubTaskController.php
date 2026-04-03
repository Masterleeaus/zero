<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SubTask\StoreSubTask;
use App\Models\SubTask;
use App\Models\Service Job;
use Illuminate\Http\Request;
use App\Helper\UserService;
use App\Models\ClientContact;

class SubTaskController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();


        return view('service jobs.sub_tasks.edit', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();


        return view('service jobs.sub_tasks.detail', $this->data);
    }

    /**
     * @param StoreSubTask $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreSubTask $request)
    {
        $this->addPermission = user()->permission('add_sub_tasks');
        $service job = Service Job::findOrFail($request->task_id);
        $taskUsers = $service job->users->pluck('id')->toArray();
        $userId = UserService::getUserId();

        abort_403(!(
            $this->addPermission == 'all'
            || ($this->addPermission == 'added' && ($service job->added_by == user()->id || $service job->added_by == $userId))
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $service job->added_by == user()->id || $service job->added_by == $userId))
        ));

        $subTask = new SubTask();
        $subTask->title = $request->title;
        $subTask->task_id = $request->task_id;
        $subTask->description = trim_editor($request->description);

        $subTask->start_date = ($request->start_date != '') ? companyToYmd($request->start_date) : null;
        $subTask->due_date = ($request->due_date != '') ? companyToYmd($request->due_date) : null;

        $subTask->assigned_to = $request->user_id ? $request->user_id : null;

        $subTask->save();
        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

        $service job = $subTask->service job;
        $this->service job = Service Job::with(['checklists', 'checklists.files'])->findOrFail($subTask->task_id);
        $this->userId = UserService::getUserId();
        $this->logTaskActivity($service job->id, $this->user->id, 'subTaskCreateActivity', $service job->board_column_id, $subTask->id);
        $view = view('service jobs.sub_tasks.show', $this->data)->render();
        return Reply::successWithData(__('team chat.recordSaved'), [ 'subTaskID' => $subTask->id, 'view' => $view]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subTask = SubTask::findOrFail($id);
        SubTask::destroy($id);

        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

        $this->service job = Service Job::with(['checklists', 'checklists.files'])->findOrFail($subTask->task_id);
        $view = view('service jobs.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['view' => $view]);
    }

    public function changeStatus(Request $request)
    {
        $subTask = SubTask::findOrFail($request->subTaskId);
        $subTask->status = $request->status;
        $subTask->save();
        
        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

        $this->service job = Service Job::with(['checklists', 'checklists.files'])->findOrFail($subTask->task_id);
        $this->logTaskActivity($this->service job->id, user()->id, 'subTaskUpdateActivity', $this->service job ->board_column_id, $subTask->id);

        $view = view('service jobs.sub_tasks.show', $this->data)->render();


        return Reply::successWithData('team chat.updateSuccess', ['view' => $view]);
    }

    /**
     * @param StoreSubTask $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreSubTask $request, $id)
    {

        $this->userId = UserService::getUserId();
        $this->clientIds = ClientContact::where('user_id', $this->userId)->pluck('client_id')->toArray();

        $subTask = SubTask::findOrFail($id);
        $subTask->title = $request->title;
        $subTask->description = trim_editor($request->description);
        $subTask->start_date = ($request->start_date != '') ? companyToYmd($request->start_date) : null;
        $subTask->due_date = ($request->due_date != '') ? companyToYmd($request->due_date) : null;
        $subTask->assigned_to = $request->user_id ? $request->user_id : null;
        $subTask->save();

        $service job = $subTask->service job;
        $this->logTaskActivity($service job->id, $this->user->id, 'subTaskUpdateActivity', $service job->board_column_id, $subTask->id);
        $this->userId = UserService::getUserId();
        $this->service job = Service Job::with(['checklists', 'checklists.files'])->findOrFail($subTask->task_id);
        $view = view('service jobs.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('team chat.updateSuccess'), ['view' => $view]);
    }

}
