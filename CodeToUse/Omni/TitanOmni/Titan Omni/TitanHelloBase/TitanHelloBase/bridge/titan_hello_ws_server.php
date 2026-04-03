<?php

declare(strict_types=1);

/**
 * Titan Hello WS Bridge (Workerman)
 *
 * This daemon terminates the Twilio Media Streams WebSocket and bridges audio to an
 * ElevenLabs Conversational AI websocket session.
 *
 * Key properties:
 * - Runs as a standalone process (Supervisor/systemd).
 * - Boots Laravel so it can read Titan Hello settings and write call events.
 * - Uses ElevenLabs signed URLs (private agents) when an API key is provided.
 *
 * References:
 * - Twilio Media Streams: start/media/stop JSON frames.
 * - ElevenLabs Agents WebSocket: client -> server expects {"user_audio_chunk":"base64"}
 *   and server -> client emits {"type":"audio", "audio_event":{ "audio_base_64":"..." }}.
 */

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;

// ----------------------------
// CLI args
// ----------------------------
$host = '127.0.0.1';
$port = 8081;
$appBase = null;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--host=')) {
        $host = (string) substr($arg, 7);
    }
    if (str_starts_with($arg, '--port=')) {
        $port = (int) substr($arg, 7);
    }
    if (str_starts_with($arg, '--app-base=')) {
        $appBase = (string) substr($arg, 11);
    }
}

// ----------------------------
// Locate + bootstrap Laravel
// ----------------------------
$findBase = function (?string $forced = null): string {
    if ($forced && is_dir($forced) && is_file($forced . '/vendor/autoload.php')) {
        return rtrim($forced, '/');
    }

    $dir = __DIR__;
    for ($i = 0; $i < 10; $i++) {
        if (is_file($dir . '/vendor/autoload.php') && is_file($dir . '/bootstrap/app.php')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    throw new RuntimeException('Unable to locate Laravel base path. Use --app-base=/path/to/app');
};

$base = $findBase($appBase);
require_once $base . '/vendor/autoload.php';

$app = require $base . '/bootstrap/app.php';
/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Resolve services we need (keep them lazy-friendly)
$settings = $app->make(\Extensions\TitanHello\Services\SettingsService::class);

// ----------------------------
// Helpers
// ----------------------------
$log = function (string $message, array $ctx = []): void {
    try {
        \Illuminate\Support\Facades\Log::info('[TitanHelloBridge] ' . $message, $ctx);
    } catch (Throwable $e) {
        // last-resort STDOUT logging
        fwrite(STDOUT, '[TitanHelloBridge] ' . $message . ' ' . json_encode($ctx) . "\n");
    }
};

// Post transcript/lead updates back into Laravel via the internal endpoints.
$postInternal = function (string $path, array $payload) use ($settings, $log): void {
    try {
        $base = rtrim((string) config('app.url'), '/');
        if ($base === '') {
            return;
        }
        $url = $base . $path;
        $secret = (string) ($settings->get('security.bridge_shared_secret') ?? config('titan-hello.security.bridge_shared_secret') ?? '');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array_filter([
                'Content-Type: application/json',
                $secret !== '' ? ('X-TitanHello-Secret: ' . $secret) : null,
            ]),
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 300) {
            $log('Internal callback non-2xx', ['path' => $path, 'code' => $code, 'resp' => $raw]);
        }
    } catch (Throwable $e) {
        $log('Internal callback failed', ['path' => $path, 'error' => $e->getMessage()]);
    }
};

/**
 * Fetch ElevenLabs signed websocket URL for a given agent id.
 *
 * If no API key is available, we assume the agent websocket is public and build a direct URL.
 *
 * NOTE: Exact signed-url endpoint is based on ElevenLabs Agents docs; if your
 * app-level ElevenLabsService already exposes a helper, prefer that instead.
 */
$getElevenWsUrl = function (string $agentId) use ($settings, $log): string {
    $apiKey = (string) ($settings->get('elevenlabs.api_key') ?? config('titan-hello.elevenlabs.api_key') ?? '');

    // Public websocket (works for public agents)
    $public = 'wss://api.elevenlabs.io/v1/convai/conversation?agent_id=' . urlencode($agentId);
    if ($apiKey === '') {
        return $public;
    }

    // Signed URL for private agents
    // Docs: /v1/convai/conversation/get_signed_url?agent_id=...
    $endpoint = 'https://api.elevenlabs.io/v1/convai/conversation/get_signed_url?agent_id=' . urlencode($agentId);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'xi-api-key: ' . $apiKey,
            'Accept: application/json',
        ],
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $code >= 300) {
        $log('Failed to get signed URL, falling back to public URL', [
            'http_code' => $code,
            'curl_error' => $err,
            'endpoint' => $endpoint,
        ]);
        return $public;
    }

    $data = json_decode((string) $raw, true);
    $signed = $data['signed_url'] ?? $data['url'] ?? null;

    if (!$signed || !is_string($signed)) {
        $log('Signed URL response missing signed_url, falling back to public URL', [
            'response' => $data,
        ]);
        return $public;
    }

    return $signed;
};

/**
 * Workerman's AsyncTcpConnection expects ws:// and uses $con->transport='ssl' for TLS.
 */
$normalizeForWorkerman = function (string $url): array {
    if (str_starts_with($url, 'wss://')) {
        $u = parse_url($url);
        $host = $u['host'] ?? '';
        $path = ($u['path'] ?? '') . (isset($u['query']) ? ('?' . $u['query']) : '');
        $port = $u['port'] ?? 443;
        return ['ws://' . $host . ':' . $port . $path, true];
    }
    return [$url, false];
};

$clientInitPayload = function () use ($settings): array {
    $userFmt = (string) ($settings->get('elevenlabs.user_input_audio_format') ?? config('titan-hello.elevenlabs.user_input_audio_format') ?? 'ulaw_8000');
    $agentFmt = (string) ($settings->get('elevenlabs.agent_output_audio_format') ?? config('titan-hello.elevenlabs.agent_output_audio_format') ?? 'ulaw_8000');

    // Inference: Agents websocket supports config overrides via conversation_initiation_client_data.
    // If ElevenLabs changes these keys, the bridge will still work for default formats.
    return [
        'conversation_initiation_client_data' => [
            'conversation_config_override' => [
                'conversation' => [
                    'user_input_audio_format' => $userFmt,
                    'agent_output_audio_format' => $agentFmt,
                ],
            ],
        ],
    ];
};

$resolveSession = function (?string $callSessionId, ?string $callSid): ?\Extensions\TitanHello\Models\TitanHelloCallSession {
    if ($callSessionId) {
        return \Extensions\TitanHello\Models\TitanHelloCallSession::query()->find($callSessionId);
    }
    if ($callSid) {
        return \Extensions\TitanHello\Models\TitanHelloCallSession::query()->where('call_sid', $callSid)->first();
    }
    return null;
};

// ----------------------------
// Workerman WS Server
// ----------------------------
$ws = new Worker("websocket://{$host}:{$port}");
$ws->name = 'titan-hello-ws-bridge';

$ws->onConnect = function (TcpConnection $conn) use ($log): void {
    // Per-connection state
    $conn->titan = [
        'streamSid' => null,
        'callSid' => null,
        'callSessionId' => null,
        'agentId' => null,
        'eleven' => null, // AsyncTcpConnection
        'eleven_ready' => false,
    ];

    // Query string params (available on HTTP upgrade request)
    try {
        $req = $conn->httpRequest;
        $conn->titan['callSid'] = $req->get('call_sid') ?: null;
        $conn->titan['callSessionId'] = $req->get('call_session_id') ?: null;
    } catch (Throwable $e) {
        // ignore
    }

    $log('WS client connected', [
        'remote' => $conn->getRemoteAddress(),
        'callSid' => $conn->titan['callSid'],
        'callSessionId' => $conn->titan['callSessionId'],
    ]);
};

$ws->onMessage = function (TcpConnection $conn, $msg) use ($log, $resolveSession, $getElevenWsUrl, $normalizeForWorkerman, $clientInitPayload): void {
    $data = json_decode((string) $msg, true);
    if (!is_array($data)) {
        return;
    }

    $event = $data['event'] ?? null;
    if (!is_string($event)) {
        return;
    }

    if ($event === 'start') {
        $callSid = (string) ($data['start']['callSid'] ?? '');
        $streamSid = (string) ($data['start']['streamSid'] ?? '');
        $params = (array) ($data['start']['customParameters'] ?? []);

        $conn->titan['callSid'] = $callSid !== '' ? $callSid : ($conn->titan['callSid'] ?? null);
        $conn->titan['streamSid'] = $streamSid !== '' ? $streamSid : null;
        $conn->titan['callSessionId'] = $params['call_session_id'] ?? ($conn->titan['callSessionId'] ?? null);

        $session = $resolveSession($conn->titan['callSessionId'], $conn->titan['callSid']);
        if ($session) {
            $conn->titan['agentId'] = (string) $session->agent_id;
            \Extensions\TitanHello\Models\TitanHelloCallEvent::query()->create([
                'call_session_id' => $session->id,
                'type' => 'stream_start',
                'payload' => $data,
            ]);
        }

        // Create ElevenLabs websocket connection
        if ($session && $conn->titan['agentId']) {
            $agent = \Extensions\TitanHello\Models\ExtVoiceChatbot::query()->find($conn->titan['agentId']);
            $remoteAgentId = (string) ($agent?->agent_id ?? '');
            if ($remoteAgentId !== '') {
                $elevenUrl = $getElevenWsUrl($remoteAgentId);
                [$wsUrl, $needsTls] = $normalizeForWorkerman($elevenUrl);
                $eleven = new AsyncTcpConnection($wsUrl);
                if ($needsTls) {
                    $eleven->transport = 'ssl';
                }

                $eleven->onWebSocketConnect = function (AsyncTcpConnection $eConn) use ($log, $clientInitPayload): void {
                    $log('ElevenLabs WS handshake complete');
                    $init = $clientInitPayload();
                    $eConn->send(json_encode($init));
                };

                $eleven->onMessage = function (AsyncTcpConnection $eConn, $eMsg) use ($conn, $log, $postInternal): void {
                    $payload = json_decode((string) $eMsg, true);
                    if (!is_array($payload)) {
                        return;
                    }

                    $type = $payload['type'] ?? null;
                    if ($type === 'audio') {
                        $audio = $payload['audio_event']['audio_base_64'] ?? null;
                        if (is_string($audio) && $audio !== '' && $conn->titan['streamSid']) {
                            $conn->send(json_encode([
                                'event' => 'media',
                                'streamSid' => $conn->titan['streamSid'],
                                'media' => [
                                    'payload' => $audio,
                                ],
                            ]));
                        }
                        return;
                    }

                    if ($type === 'agent_response') {
                        $text = $payload['agent_response_event']['agent_response'] ?? null;
                        $log('Eleven agent_response', ['text' => $text]);

                        if (is_string($text) && $text !== '' && ($conn->titan['callSid'] ?? null)) {
                            $postInternal('/api/titan-hello/internal/transcript', [
                                'call_sid' => (string) $conn->titan['callSid'],
                                'role' => 'assistant',
                                'message' => $text,
                            ]);
                        }
                        return;
                    }

                    if ($type === 'user_transcript') {
                        $text = $payload['user_transcription_event']['user_transcript'] ?? null;
                        $log('Eleven user_transcript', ['text' => $text]);

                        if (is_string($text) && $text !== '' && ($conn->titan['callSid'] ?? null)) {
                            $postInternal('/api/titan-hello/internal/transcript', [
                                'call_sid' => (string) $conn->titan['callSid'],
                                'role' => 'user',
                                'message' => $text,
                            ]);
                        }
                        return;
                    }
                };

                $eleven->onClose = function (AsyncTcpConnection $eConn) use ($log): void {
                    $log('ElevenLabs WS closed');
                };

                $eleven->onError = function (AsyncTcpConnection $eConn, $code, $msg) use ($log): void {
                    $log('ElevenLabs WS error', ['code' => $code, 'message' => $msg]);
                };

                $eleven->connect();

                $conn->titan['eleven'] = $eleven;
            } else {
                $log('No remote ElevenLabs agent_id on ExtVoiceChatbot; cannot start conversation');
            }
        }

        return;
    }

    if ($event === 'media') {
        $audio = $data['media']['payload'] ?? null;
        if (!is_string($audio) || $audio === '') {
            return;
        }

        /** @var AsyncTcpConnection|null $eleven */
        $eleven = $conn->titan['eleven'] ?? null;
        if (!$eleven) {
            return;
        }

        // Send audio chunk to ElevenLabs
        $eleven->send(json_encode([
            'user_audio_chunk' => $audio,
        ]));

        return;
    }

    if ($event === 'stop') {
        $callSid = (string) ($data['stop']['callSid'] ?? ($conn->titan['callSid'] ?? ''));
        $session = $resolveSession($conn->titan['callSessionId'] ?? null, $callSid !== '' ? $callSid : null);

        if ($session) {
            \Extensions\TitanHello\Models\TitanHelloCallEvent::query()->create([
                'call_session_id' => $session->id,
                'type' => 'stream_stop',
                'payload' => $data,
            ]);

            $session->status = 'completed';
            $session->ended_at = now();
            $session->save();
        }

        // close eleven
        if ($conn->titan['eleven'] instanceof AsyncTcpConnection) {
            $conn->titan['eleven']->close();
        }

        $conn->close();
        return;
    }
};

$ws->onClose = function (TcpConnection $conn) use ($log): void {
    if (isset($conn->titan['eleven']) && $conn->titan['eleven'] instanceof AsyncTcpConnection) {
        $conn->titan['eleven']->close();
    }

    $log('WS client disconnected', [
        'remote' => $conn->getRemoteAddress(),
        'callSid' => $conn->titan['callSid'] ?? null,
    ]);
};

$log('Starting Titan Hello WS bridge', [
    'host' => $host,
    'port' => $port,
    'laravel_base' => $base,
]);

Worker::runAll();
