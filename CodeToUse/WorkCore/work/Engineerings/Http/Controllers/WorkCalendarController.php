<?php

namespace Modules\Engineerings\Http\Controllers;

use App\Helper\Reply;
use App\Models\Event;
use App\Http\Controllers\AccountBaseController;
use Modules\Engineerings\Entities\WorkOrder;

class WorkCalendarController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'engineerings::modules.calendar';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        $permissions = user()->permission('view_eng');
        abort_403(!in_array($permissions, ['all', 'added', 'owned', 'both']));
        $this->jadwals           = WorkOrder::all();
        $this->data['eventData'] = [];

        foreach ($this->jadwals as $jadwal) {
            $this->data['eventData'][] = [
                'id'    => $jadwal->id,
                'title' => ucfirst($jadwal->work_description),
                'start' => $jadwal->schedule_start,
                'end'   => $jadwal->schedule_finish,
                'color' => '#ff0000',
            ];
        }

        return view('engineerings::calendar.index', $this->data);
    }

    public function show($id)
    {
        $this->permissions = user()->permission('view_eng');
        $this->event       = Event::with('attendee', 'attendee.user')->findOrFail($id);
        $attendeesIds      = $this->event->attendee->pluck('user_id')->toArray();

        abort_403(!($this->permissions == 'all' || ($this->permissions == 'added' && $this->event->added_by == user()->id) || ($this->permissions == 'owned' && in_array(user()->id, $attendeesIds)) || ($this->permissions == 'both' && (in_array(user()->id, $attendeesIds) || $this->event->added_by == user()->id))));

        $this->pageTitle = __('app.menu.Events') . ' ' . __('app.details');

        if (request()->ajax()) {
            $html = view('event-calendar.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'event-calendar.ajax.show';

        return view('event-calendar.create', $this->data);
    }
}
