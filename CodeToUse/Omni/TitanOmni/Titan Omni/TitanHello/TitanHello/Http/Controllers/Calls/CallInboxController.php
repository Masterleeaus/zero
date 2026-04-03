<?php

namespace Modules\TitanHello\Http\Controllers\Calls;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanHello\Models\Call;

class CallInboxController extends Controller
{
    public function index(Request $request)
    {
        $q = Call::query();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status')->toString());
        }

        if ($request->filled('direction')) {
            $q->where('direction', $request->string('direction')->toString());
        }

        if ($request->filled('disposition')) {
            $q->where('disposition', $request->string('disposition')->toString());
        }

        if ($request->filled('assigned')) {
            if ($request->string('assigned')->toString() === 'unassigned') {
                $q->whereNull('assigned_to_user_id');
            } elseif (is_numeric($request->get('assigned'))) {
                $q->where('assigned_to_user_id', (int) $request->get('assigned'));
            }
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->get('q'));
            $q->where(function ($sub) use ($term) {
                $sub->where('from_number', 'like', "%{$term}%")
                    ->orWhere('to_number', 'like', "%{$term}%")
                    ->orWhere('provider_call_sid', 'like', "%{$term}%");
            });
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->get('to'));
        }

        $calls = $q->orderByDesc('id')->paginate(50)->withQueryString();

        $filter = $request->only(['status','direction','disposition','assigned','q','from','to']);

        return view('titanhello::calls.index', compact('calls', 'filter'));
    }

    public function show($id)
    {
        $call = Call::query()->with(['events', 'recordings', 'notes'])->findOrFail($id);
        return view('titanhello::calls.show', compact('call'));
    }

    public function assign(Request $request, $id)
    {
        $call = Call::query()->findOrFail($id);
        $call->assigned_to_user_id = $request->filled('assigned_to_user_id') ? (int) $request->get('assigned_to_user_id') : null;
        $call->save();

        return redirect()->route('titanhello.calls.show', ['id' => $call->id])->with('success', 'Assignment updated.');
    }

    public function disposition(Request $request, $id)
    {
        $call = Call::query()->findOrFail($id);
        $call->disposition = $request->filled('disposition') ? (string) $request->get('disposition') : null;
        $call->disposition_notes = $request->filled('disposition_notes') ? (string) $request->get('disposition_notes') : null;
        $call->save();

        return redirect()->route('titanhello.calls.show', ['id' => $call->id])->with('success', 'Disposition saved.');
    }

    public function bulk(Request $request)
    {
        $ids = (array) $request->get('ids', []);
        $action = (string) $request->get('action', '');

        if (!$ids || !$action) {
            return redirect()->route('titanhello.calls.index');
        }

        $calls = Call::query()->whereIn('id', $ids);

        if ($action === 'mark_spam') {
            $calls->update(['disposition' => 'spam']);
        }

        if ($action === 'clear_assignment') {
            $calls->update(['assigned_to_user_id' => null]);
        }

        return redirect()->route('titanhello.calls.index')->with('success', 'Bulk action applied.');
    }
}
