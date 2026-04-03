<?php

namespace Modules\TitanHello\Http\Controllers\Calls;

use Illuminate\Routing\Controller;
use Modules\TitanHello\Http\Requests\OutboundCallRequest;
use Modules\TitanHello\Services\Calls\OutboundCallService;

class DialerController extends Controller
{
    public function index()
    {
        return view('titanhello::calls.dialer');
    }

    public function call(OutboundCallRequest $request, OutboundCallService $outbound)
    {
        $call = $outbound->placeCall(
            (string) $request->input('to_number'),
            (string) ($request->input('from_number') ?? '')
        );

        return redirect()->route('titanhello.calls.show', $call->id)
            ->with('success', 'Outbound call queued.');
    }
}
