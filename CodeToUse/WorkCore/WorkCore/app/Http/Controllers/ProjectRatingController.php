<?php

namespace App\Http\Controllers;

use App\Events\NewProjectEvent;
use App\Helper\Reply;
use App\Http\Requests\Site\StoreRating;
use App\Models\ProjectRating;
use Illuminate\Http\Request;

class ProjectRatingController extends AccountBaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRating $request)
    {
        $addProjectRatingPermission = user()->permission('add_project_rating');
        abort_403(!in_array($addProjectRatingPermission, ['all', 'added', 'owned', 'both']));

        $rating = new ProjectRating();
        $rating->rating = $request->rating;
        $rating->comment = $request->comment;
        $rating->user_id = $this->user->id;
        $rating->project_id = $request->project_id;
        $rating->added_by = user()->id;
        $rating->last_updated_by = user()->id;
        $rating->save();

        $members = $rating->site->projectMembers;

        event(new NewProjectEvent($rating->site, $members, 'ProjectRating'));

        return Reply::success(__('team chat.recordSaved'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRating $request, $id)
    {

        $addProjectRatingPermission = user()->permission('edit_project_rating');
        abort_403(!in_array($addProjectRatingPermission, ['all', 'added', 'owned', 'both']));

        $rating = ProjectRating::findOrFail($id);
        $rating->rating = $request->rating;
        $rating->comment = $request->comment;
        $rating->user_id = $this->user->id;
        $rating->project_id = $request->project_id;
        $rating->added_by = user()->id;
        $rating->last_updated_by = user()->id;
        $rating->save();

        return Reply::success(__('team chat.updateSuccess'));

    }

    public function destroy(Request $request, $id)
    {
        $deleteRatingPermission = user()->permission('delete_project_rating');
        $rating  = ProjectRating::findOrFail($id);
        $memberIds = $rating->site->members->pluck('user_id')->toArray();

        abort_403(
            !(is_null($rating->site->deleted_at) &&
            $deleteRatingPermission == 'all'
            || ($deleteRatingPermission == 'added' && $rating->site->rating->added_by == user()->id)
            || ($deleteRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $rating->site->client_id == user()->id))
            || ($deleteRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $rating->site->client_id == user()->id || $rating->site->rating->added_by == user()->id))
            || in_array('customer', user_roles()))
        );

        ProjectRating::destroy($id);
        return Reply::success(__('team chat.deleteSuccess'));
        
    }

}
