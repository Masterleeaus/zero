# Titan Hello – Twilio ↔ ElevenLabs WebSocket Bridge (Workerman)

Twilio Media Streams requires a **WebSocket (ws/wss) server**.
Laravel HTTP routes cannot serve WebSockets, so Titan Hello ships a **standalone Workerman daemon** that:

- Accepts Twilio JSON frames (`start`, `media`, `stop`)
- Resolves the `titan_hello_call_sessions` row (via `call_session_id` + `call_sid`)
- Opens an ElevenLabs Agents websocket session
- Forwards caller audio → ElevenLabs (`{"user_audio_chunk":"..."}`)
- Forwards agent audio → Twilio (`{"event":"media","media":{"payload":"..."}}`)

## Install bridge dependencies
From `extensions/titan-hello/bridge`:

```bash
composer install --no-dev
```

## Run the daemon
Example:

```bash
php extensions/titan-hello/bridge/titan_hello_ws_server.php --host=127.0.0.1 --port=8081
```

The daemon boots Laravel automatically by walking up directories until it finds `vendor/autoload.php`.
If your paths are unusual, provide:

```bash
php .../titan_hello_ws_server.php --app-base=/var/www/html
```

## Reverse proxy (recommended)
Terminate TLS at NGINX/Caddy and proxy to the daemon.

- Public: `wss://YOUR_DOMAIN/api/titan-hello/twilio/voice/stream`
- Internal: `ws://127.0.0.1:8081`

## ElevenLabs auth
- **Public agents:** no API key required.
- **Private agents:** set `elevenlabs.api_key` in Titan Hello settings so the daemon can fetch a signed websocket URL.

## Audio formats
Twilio streams **μ-law 8kHz** by default.
The daemon sends a `conversation_config_override` init message using:
- `elevenlabs.user_input_audio_format` (default `ulaw_8000`)
- `elevenlabs.agent_output_audio_format` (default `ulaw_8000`)

If ElevenLabs changes override keys, the bridge still works *if* the agent is configured server-side to match Twilio.

## Internal callbacks (transcripts + leads)
The daemon posts back into Laravel so your admin UI can show call transcripts and extracted lead fields.

- Transcript chunks:
  - `POST /api/titan-hello/internal/transcript`
  - JSON: `{ "call_sid": "...", "role": "user|assistant", "message": "..." }`

- Structured lead updates:
  - `POST /api/titan-hello/internal/lead`
  - JSON: `{ "call_sid": "...", "lead": { "caller_name": "...", "suburb": "...", "job_type": "...", "urgency": "high|medium|low", "callback_window": "today|tomorrow|...", "notes": "..." } }`

If you set `titan-hello.security.bridge_shared_secret`, include:
- Header: `X-TitanHello-Secret: <secret>`
