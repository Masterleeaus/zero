<?php

namespace Modules\TitanHello\Http\Controllers\Callbacks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanHello\Models\CallbackRequest;

class CallbackInboxController extends Controller
{
    public function index(Request $request)
    {
        $companyId = (int)($request->get('company_id') ?? 0);

        $q = CallbackRequest::query()->where('company_id', $companyId);

        if ($status = $request->get('status')) {
            $q->where('status', $status);
        } else {
            $q->where('status', 'open');
        }

        if ($priority = $request->get('priority')) {
            $q->where('priority', $priority);
        }

        if ($assigned = $request->get('assigned_to')) {
            $q->where('assigned_to', (int)$assigned);
        }

        if ($due = $request->get('due')) {
            if ($due === 'overdue') {
                $q->where('due_at', '<', now());
            }
            if ($due === 'today') {
                $q->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()]);
            }
        }

        $callbacks = $q->orderBy('due_at')->paginate(25)->withQueryString();

        return view('titanhello::callbacks.index', [
            'callbacks' => $callbacks,
            'filters' => $request->all(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $companyId = (int)($request->get('company_id') ?? 0);

        $cb = CallbackRequest::query()
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();

        return view('titanhello::callbacks.show', [
            'cb' => $cb,
        ]);
    }
}
