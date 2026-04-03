<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Site\StoreProjectNote;
use App\Models\Site;
use App\Models\ProjectNote;
use App\Models\ProjectUserNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProjectNoteController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.sites';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('sites', $this->user->modules));
            return $next($request);
        });
    }

    public function create()
    {
        $this->viewProjectPermission = user()->permission('add_project_note');
        $this->site = Site::findOrFail(request('site'));
        abort_403(!(in_array($this->viewProjectPermission, ['all']) || $this->site->project_admin == user()->id));


        $this->cleaners = $this->site->projectMembers;

        $this->pageTitle = __('app.addProjectNote');

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'sites.notes.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('sites.create', $this->data);
    }

    public function store(StoreProjectNote $request)
    {
        $this->addProjectPermission = user()->permission('add_project_note');
        $this->site = Site::findOrFail(request('project_id'));

        abort_403(!(in_array($this->addProjectPermission, ['all']) || $this->site->project_admin == user()->id));

        $note = new ProjectNote();
        $note->title = $request->title;
        $note->project_id = $request->project_id;
        $note->details = $request->details;
        $note->type = $request->type;
        $note->client_id = $request->client_id ?? null;
        $note->is_client_show = $request->is_client_show ? $request->is_client_show : '';
        $note->ask_password = $request->ask_password ? $request->ask_password : '';
        $note->save();

        /* if note type is private */
        if ($request->type == 1) {
            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    ProjectUserNote::firstOrCreate([
                        'user_id' => $user,
                        'project_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['redirectUrl' => route('sites.show', $note->project_id) . '?tab=notes']);


    }

    public function show($id)
    {

        $this->note = ProjectNote::with('site')->findOrFail($id);

        if ($this->note->type == 1) {
            $this->cleaners = $this->note->noteUsers;

        } else {
            $this->cleaners = $this->note->site->projectMembers; /** @phpstan-ignore-line */
        }

        $memberIds = $this->note->site->members->pluck('user_id')->toArray(); /** @phpstan-ignore-line */

        $this->viewPermission = user()->permission('view_projects');
        $viewProjectNotePermission = user()->permission('view_project_note');

        abort_403(!(
            $viewProjectNotePermission == 'all'
            || $this->note->type == 0
            /** @phpstan-ignore-next-line */
            || ($this->note->site && $this->note->site->project_admin == user()->id)
            || ($viewProjectNotePermission == 'added' && $this->note->added_by == user()->id)

            || ($viewProjectNotePermission == 'owned' && $this->note->client_id == user()->id) /* @phpstan-ignore-line */
            || ($viewProjectNotePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('cleaner', user_roles()))

            || ($viewProjectNotePermission == 'both' && (user()->id == $this->note->client_id || $this->note->added_by == user()->id || in_array(user()->id, $memberIds)))/* @phpstan-ignore-line */
        ));

        $this->pageTitle = $this->note->title;

        $this->view = 'sites.notes.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return redirect(route('sites.show', $this->note->project_id) . '?tab=notes');

    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_project_note');
        $this->note = ProjectNote::with('site')->findOrFail($id);
        $this->cleaners = $this->note->site->projectMembers; /** @phpstan-ignore-line */
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->projectId = $this->note->project_id;
        $projectuserData = [];

        $usersData = $this->note->site->projectMembers;

        foreach ($usersData as $user) {
            $url = route('cleaners.show', [$user->id]);

            $projectuserData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->projectuserData = $projectuserData;

        abort_403(!in_array($this->editPermission, ['all', 'added', 'owned', 'both']));

        $this->pageTitle = __('app.editProjectNote');

        $this->view = 'sites.notes.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('sites.create', $this->data);
    }

    public function update(StoreProjectNote $request, $id)
    {
        $note = ProjectNote::findOrFail($id);
        $note->title = $request->title;
        $note->project_id = $request->project_id;
        $note->details = $request->details;
        $note->type = $request->type;
        $note->is_client_show = $request->is_client_show ?: '';
        $note->ask_password = $request->ask_password ?: '';
        $note->save();

        // delete all data od this project_note_id from client_user_notes
        ProjectUserNote::where('project_note_id', $note->id)->delete();

        /* if note type is private */
        if ($request->type == 1) {
            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    ProjectUserNote::firstOrCreate([
                        'user_id' => $user,
                        'project_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => route('sites.show', $note->project_id) . '?tab=notes']);

    }

    public function destroy($id)
    {

        $this->contact = ProjectNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_project_note');

        if (
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->contact->added_by == user()->id)
        ) {
            $this->contact->delete();
        }

        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('team chat.deleteSuccess'));
        default:
                return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_project_note') !== 'all');
        ProjectNote::whereIn('id', explode(',', $request->row_ids))->delete();
        return true;
    }

    public function askForPassword($id)
    {
        $this->note = ProjectNote::findOrFail($id);
        $this->formType = request()->form_type;
        return view('sites.notes.verify-password', $this->data);
    }

    public function checkPassword(Request $request)
    {
        $this->customer = User::findOrFail($this->user->id);

        return Hash::check($request->password, $this->customer->password) ? Reply::success(__('team chat.passwordMatched')) : Reply::error(__('team chat.incorrectPassword'));
    }

}
