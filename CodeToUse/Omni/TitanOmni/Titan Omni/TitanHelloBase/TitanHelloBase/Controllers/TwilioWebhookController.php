<?php

namespace Extensions\TitanHello\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Extensions\TitanHello\Services\SettingsService;
use Extensions\TitanHello\Models\TitanHelloCallSession;
use Extensions\TitanHello\Models\TitanHelloCallEvent;
use Extensions\TitanHello\Models\TitanHelloPhoneNumber;
use Extensions\TitanHello\Services\LeadExtractionService;
use Extensions\TitanHello\Services\CallSummaryService;
use Extensions\TitanHello\Services\TwilioSmsService;
use Extensions\TitanHello\Models\ExtVoiceChatbot;

class TwilioWebhookController extends Controller
{
    public function inbound(Request $request, SettingsService $settings): Response
    {
        // NOTE: Signature verification + throttle will be added in Hardening pass.

        $callSid = (string) $request->input('CallSid', '');
        $from = (string) $request->input('From', '');
        $to = (string) $request->input('To', '');
        $direction = (string) $request->input('Direction', 'inbound');
        $callStatus = (string) $request->input('CallStatus', 'ringing');

        if ($callSid === '') {
            return response('Missing CallSid', 400);
        }

        // Resolve target phone number record (optional)
        $phoneNumber = TitanHelloPhoneNumber::query()
            ->where('phone_number', $to)
            ->first();

        // Resolve agent: phone mapping > first active agent
        $agentId = $phoneNumber?->agent_id;
        if (!$agentId) {
            $agentId = ExtVoiceChatbot::query()->where('status', 1)->value('id');
        }

        $session = TitanHelloCallSession::query()->firstOrCreate(
            ['call_sid' => $callSid],
            [
                'from_number' => $from,
                'to_number' => $to,
                'direction' => $direction,
                'status' => $callStatus,
                'phone_number_id' => $phoneNumber?->id,
                'agent_id' => $agentId,
                'started_at' => now(),
                'recording_enabled' => (bool) $settings->get('recording.enabled', false),
            ]
        );

        TitanHelloCallEvent::query()->create([
            'call_session_id' => $session->id,
            'type' => 'ringing',
            'payload' => [
                'from' => $from,
                'to' => $to,
                'status' => $callStatus,
            ],
        ]);

        $agent = $agentId ? ExtVoiceChatbot::query()->find($agentId) : null;

        // Ensure a conversation exists for this call (so transcripts can be appended).
        if ($agent && !$session->conversation_db_id) {
            $conversation = \Extensions\TitanHello\Models\ExtVoicechabotConversation::query()->create([
                'chatbot_uuid' => $agent->uuid,
                'conversation_id' => $callSid,
            ]);
            $session->conversation_db_id = $conversation->id;
            $session->save();
        }


        $consent = (string) $settings->get('recording.consent_message', 'This call may be recorded for quality and proof of work.');
        $greeting = $agent?->welcome_message ?: 'Hi, thanks for calling. I can take a message and help organise your job.';

        $inHours = $this->isWithinBusinessHours($settings);

        // Persist routing context for later automation
        $meta = is_array($session->meta) ? $session->meta : (array) $session->meta;
        $meta['after_hours'] = !$inHours;
        $session->meta = $meta;
        $session->save();

        $afterHoursMode = (string) $settings->get('routing.after_hours_mode', 'take_message');
        $forwardNumber = (string) $settings->get('routing.forward_number', '');
        $voicemailMax = (int) $settings->get('routing.voicemail.max_length', 120);
        $voicemailBeep = (bool) $settings->get('routing.voicemail.play_beep', true);


        // For now we return TwiML that greets and starts a media stream.
        // The WebSocket bridge endpoint will be implemented in the next step.
        $publicWs = (string) $settings->get('bridge.public_ws_url', '');
        if ($publicWs === '') {
            $appUrl = rtrim((string) config('app.url'), '/');
            // Twilio <Stream> requires ws:// or wss://.
            // Convert the app base URL scheme:
            //   https://example.com -> wss://example.com
            //   http://example.com  -> ws://example.com
            $wsBase = preg_replace('/^https:/i', 'wss:', $appUrl);
            $wsBase = preg_replace('/^http:/i', 'ws:', $wsBase);
            $publicWs = $wsBase . '/api/titan-hello/twilio/voice/stream';
        }
        $streamUrl = rtrim($publicWs, '/')
            . '?call_sid=' . urlencode($callSid)
            . '&call_session_id=' . urlencode((string) $session->id);

        $twimlData = [
            // Speak consent only if recording enabled
            'say' => $session->recording_enabled ? $consent : null,
            'greeting' => $greeting,
        ];

        if ($inHours) {
            $twimlData['stream_url'] = $streamUrl;
            $twimlData['stream_params'] = [
                'call_sid' => $callSid,
                'call_session_id' => (string) $session->id,
            ];
        } elseif ($afterHoursMode === 'forward' && $forwardNumber !== '') {
            $twimlData['dial_number'] = $forwardNumber;
        } else {
            // Take a voicemail style message (recording enabled/disabled is separate; this is the after-hours capture).
            $recordActionBase = rtrim((string) config('app.url'), '/');
            $twimlData['record'] = true;
            $twimlData['record_max_length'] = $voicemailMax;
            $twimlData['record_beep'] = $voicemailBeep;
            $twimlData['record_action'] = $recordActionBase . '/api/titan-hello/twilio/voice/recording?call_sid=' . urlencode($callSid) . '&call_session_id=' . urlencode((string) $session->id);
        }

        $twiml = $this->twimlResponse($twimlData);

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    public function status(Request $request): Response
    {
        $callSid = (string) $request->input('CallSid', '');
        $callStatus = (string) $request->input('CallStatus', '');

        if ($callSid === '') {
            return response('Missing CallSid', 400);
        }

        $session = TitanHelloCallSession::query()->where('call_sid', $callSid)->first();
        if (!$session) {
            return response('Not found', 404);
        }

        if ($callStatus !== '') {
            $session->status = $callStatus;
            if (in_array($callStatus, ['completed', 'canceled', 'failed', 'busy', 'no-answer'], true)) {
                $session->ended_at = now();
            }
            $session->save();

            TitanHelloCallEvent::query()->create([
                'call_session_id' => $session->id,
                'type' => 'status',
                'payload' => ['status' => $callStatus],
            ]);
        }


        // On terminal states, finalise summary + extract lead fields + optional follow-up
        if (in_array($session->status, ['completed', 'canceled', 'failed', 'busy', 'no-answer'], true)) {
            app(LeadExtractionService::class)->extractAndUpdate($session);
            app(CallSummaryService::class)->finalise($session);

            $followEnabled = (bool) app(SettingsService::class)->get('followup.sms_enabled', false);
            $afterHours = (bool) (is_array($session->meta) && array_key_exists('after_hours', $session->meta) ? $session->meta['after_hours'] : false);
            if ($followEnabled && $session->from_number && ($afterHours || in_array($session->status, ['no-answer', 'busy', 'failed'], true))) {
                $template = (string) app(SettingsService::class)->get('followup.sms_template', 'Thanks for calling. We\'ve received your enquiry and will get back to you shortly.');
                app(TwilioSmsService::class)->send($session->from_number, $template);
            }
        }

        return response('ok', 200);
    }

    public function recording(Request $request): Response
    {
        $callSid = (string) $request->input('CallSid', '');
        $recordingUrl = (string) $request->input('RecordingUrl', '');

        if ($callSid === '') {
            return response('Missing CallSid', 400);
        }

        $session = TitanHelloCallSession::query()->where('call_sid', $callSid)->first();
        if (!$session) {
            return response('Not found', 404);
        }

        if ($recordingUrl !== '') {
            $session->recording_url = $recordingUrl;
            $session->save();

            TitanHelloCallEvent::query()->create([
                'call_session_id' => $session->id,
                'type' => 'recording',
                'payload' => ['recording_url' => $recordingUrl],
            ]);
        }

        return response('ok', 200);
    }

    private function twimlResponse(array $data): string
    {
        $say1 = isset($data['say']) && $data['say'] ? $this->xmlEscape((string) $data['say']) : null;
        $greeting = $this->xmlEscape((string) ($data['greeting'] ?? 'Hi.'));
        $streamUrl = $this->xmlEscape((string) ($data['stream_url'] ?? ''));

        // TwiML: https://www.twilio.com/docs/voice/twiml
        // Media Streams: <Connect><Stream url="wss://..."/></Connect>
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response>\n";
        if ($say1) {
            $xml .= "  <Say>" . $say1 . "</Say>\n";
        }
        $xml .= "  <Say>" . $greeting . "</Say>\n";
        $dialNumber = isset($data['dial_number']) && $data['dial_number'] ? $this->xmlEscape((string) $data['dial_number']) : null;
        $doRecord = (bool) ($data['record'] ?? false);
        $recordMax = (int) ($data['record_max_length'] ?? 120);
        $recordBeep = (bool) ($data['record_beep'] ?? true);
        $recordAction = isset($data['record_action']) && $data['record_action'] ? $this->xmlEscape((string) $data['record_action']) : null;

        if ($dialNumber) {
            $xml .= "  <Dial>" . $dialNumber . "</Dial>
";
        } elseif ($doRecord) {
            $attrs = " maxLength=\"" . $recordMax . "\"";
            $attrs .= $recordBeep ? " playBeep=\"true\"" : " playBeep=\"false\"";
            if ($recordAction) {
                $attrs .= " action=\"" . $recordAction . "\" method=\"POST\"";
            }
            $xml .= "  <Record" . $attrs . " />
";
        } elseif ($streamUrl !== '') {
            $xml .= "  <Connect>
";
            $xml .= "    <Stream url=\"" . $streamUrl . "\">
";
            $params = $data['stream_params'] ?? [];
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    $xml .= "      <Parameter name=\"" . $this->xmlEscape((string) $k) . "\" value=\"" . $this->xmlEscape((string) $v) . "\" />
";
                }
            }
            $xml .= "    </Stream>
";
            $xml .= "  </Connect>
";
        } else {
            $xml .= "  <Pause length=\"60\" />
";
        }


        $xml .= "</Response>";
        return $xml;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
