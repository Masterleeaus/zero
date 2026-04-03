<?php

namespace Modules\TitanHello\Http\Controllers\Calls;

use Illuminate\Routing\Controller;
use Modules\TitanHello\Http\Requests\AssignCallRequest;
use Modules\TitanHello\Http\Requests\CallbackCallRequest;
use Modules\TitanHello\Http\Requests\DispositionCallRequest;
use Modules\TitanHello\Http\Requests\NoteCallRequest;
use Modules\TitanHello\Models\Call;
use Modules\TitanHello\Models\CallNote;

class CallActionsController extends Controller
{
    public function assign($id, AssignCallRequest $request)
    {
        $call = Call::query()->findOrFail($id);
        $call->assigned_to_user_id = $request->input('assigned_to_user_id');
        $call->assigned_at = now();
        $call->save();

        return redirect()->route('titanhello.calls.show', $call->id)->with('success', 'Assigned.');
    }

    public function setDisposition($id, DispositionCallRequest $request)
    {
        $call = Call::query()->findOrFail($id);
        $call->disposition = $request->input('disposition');
        $call->disposition_notes = $request->input('disposition_notes');
        if ($request->filled('priority')) {
            $call->priority = (int) $request->input('priority');
        }
        $call->save();

        return redirect()->route('titanhello.calls.show', $call->id)->with('success', 'Updated.');
    }

    public function setCallback($id, CallbackCallRequest $request)
    {
        $call = Call::query()->findOrFail($id);
        $call->callback_due_at = $request->input('callback_due_at');
        $call->save();

        return redirect()->route('titanhello.calls.show', $call->id)->with('success', 'Callback scheduled.');
    }

    public function addNote($id, NoteCallRequest $request)
    {
        $call = Call::query()->findOrFail($id);

        CallNote::create([
            'call_id' => $call->id,
            'user_id' => auth()->id(),
            'note' => $request->input('note'),
        ]);

        return redirect()->route('titanhello.calls.show', $call->id)->with('success', 'Note added.');
    }
}
