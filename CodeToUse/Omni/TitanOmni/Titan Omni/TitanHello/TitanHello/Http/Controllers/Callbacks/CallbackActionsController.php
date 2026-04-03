<?php

namespace Modules\TitanHello\Http\Controllers\Callbacks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanHello\Models\CallbackRequest;
use Modules\TitanHello\Services\Callbacks\CallbackService;

class CallbackActionsController extends Controller
{
    public function assign(Request $request, int $id)
    {
        $companyId = (int)($request->get('company_id') ?? 0);
        $cb = CallbackRequest::query()->where('company_id', $companyId)->findOrFail($id);

        $cb->assigned_to = (int)$request->input('assigned_to');
        $cb->save();

        return back()->with('status', 'Callback assigned.');
    }

    public function setDue(Request $request, int $id)
    {
        $companyId = (int)($request->get('company_id') ?? 0);
        $cb = CallbackRequest::query()->where('company_id', $companyId)->findOrFail($id);

        $cb->due_at = $request->input('due_at') ? \Carbon\Carbon::parse($request->input('due_at')) : $cb->due_at;
        $cb->priority = $request->input('priority', $cb->priority);
        $cb->save();

        return back()->with('status', 'Callback updated.');
    }

    public function done(Request $request, int $id, CallbackService $callbacks)
    {
        $companyId = (int)($request->get('company_id') ?? 0);
        $cb = CallbackRequest::query()->where('company_id', $companyId)->findOrFail($id);

        $callbacks->markDone($cb, (int)($request->user()->id ?? 0));

        return redirect()->route('titanhello.callbacks.index', ['company_id' => $companyId])->with('status', 'Callback marked done.');
    }

    public function cancel(Request $request, int $id, CallbackService $callbacks)
    {
        $companyId = (int)($request->get('company_id') ?? 0);
        $cb = CallbackRequest::query()->where('company_id', $companyId)->findOrFail($id);

        $callbacks->cancel($cb);

        return redirect()->route('titanhello.callbacks.index', ['company_id' => $companyId])->with('status', 'Callback cancelled.');
    }
}
