<?php

namespace Modules\TitanHello\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanHello\Services\Calls\CallIngestService;
use Modules\TitanHello\Services\Providers\Twilio\TwilioProvider;
use Modules\TitanHello\Services\Routing\InboundRoutingService;
use Modules\TitanHello\Services\Callbacks\CallOutcomeResolver;
use Modules\TitanHello\Jobs\AutoCreateCallbackJob;

class CallWebhookController extends Controller
{
    public function inbound(Request $request, CallIngestService $ingest, TwilioProvider $provider, InboundRoutingService $routing)
    {
        try {
            $ok = $provider->validateSignature($request->headers->all(), $request->fullUrl(), $request->all());
            if (!$ok && config('titanhello.twilio.require_signature', false)) {
                return response('Invalid signature', 403);
            }
        } catch (\Throwable $e) {
            // fail-open unless explicitly required
        }

        $attrs = $provider->mapInbound($request->all());
        $call = $ingest->upsertCall($attrs);
        $ingest->addEvent($call, 'inbound', 'ringing', $request->all(), now());

        // Resolve tenant/company by the "To" number (DID).
        $companyId = $routing->resolveCompanyIdByToNumber((string)($attrs['to_number'] ?? '')) ?? (int)($call->company_id ?? 0);
        if ($companyId && !$call->company_id) {
            $call->company_id = $companyId;
            $call->save();
        }

        $twiml = $routing->buildInboundResponse($companyId ?: 0, (string)($attrs['to_number'] ?? ''), preg_replace('#/inbound$#', '', rtrim(route('titanhello.webhooks.voice.inbound'), '/')));

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    public function ivrSelect(Request $request, int $menuId, InboundRoutingService $routing)
    {
        $digit = (string) ($request->input('Digits') ?? '');
        $to = (string) ($request->input('To') ?? '');
        $companyId = $routing->resolveCompanyIdByToNumber($to) ?? 0;

        $twiml = $routing->buildIvrSelectionResponse($companyId, $menuId, $digit, preg_replace('#/inbound$#', '', rtrim(route('titanhello.webhooks.voice.inbound'), '/')));

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    public function outboundTwiml(Request $request)
    {
        // Minimal outbound TwiML: connect and optionally record.
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response>'
            . '<Say voice="alice">Connecting your call.</Say>'
            . '</Response>';

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    public function status(Request $request, CallIngestService $ingest, TwilioProvider $provider, CallOutcomeResolver $outcomes)
    {
        $attrs = $provider->mapStatus($request->all());
        $call = $ingest->upsertCall(array_filter($attrs, fn($v) => $v !== null));
        $ingest->addEvent($call, 'status', (string)($attrs['status'] ?? 'status'), $request->all(), now());

        // Apply canonical outcomes (answered/missed/failed)
        $outcomes->apply($call, (string)($attrs['status'] ?? null));

        // Auto-create callback for missed calls (async)
        if (($call->call_outcome ?? '') === 'missed') {
            AutoCreateCallbackJob::dispatch($call->id);
        }

        return response('OK', 200);
    }

    public function recording(Request $request, CallIngestService $ingest, TwilioProvider $provider)
    {

$attrs = $provider->mapRecording($request->all());
$call = $ingest->upsertCall(array_filter($attrs, fn($v) => $v !== null));
$rec = $ingest->addRecording($call, $attrs);

// Fetch and store the media asynchronously.
try {
    FetchCallRecordingJob::dispatch($rec->id);
} catch (\Throwable $e) {
    // ignore queue failures
}

// If this recording is a voicemail, mark the call and optionally request a Titan Zero summary (artifacted).
if (($rec->kind ?? 'call') === 'voicemail') {
    $call->voicemail_flag = true;
    $call->voicemail_received_at = now();
    $call->voicemail_recording_id = $rec->id;
    $call->save();

    if (config('titanhello.voicemail.request_titan_zero_summary', false)) {
        try {
            RequestVoicemailSummaryFromTitanZeroJob::dispatch($call->id, $rec->id);
        } catch (\Throwable $e) {
            // ignore queue failures
        }
    }
}

return response('OK', 200);

    }
}
