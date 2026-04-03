# Titan Hello (MagicAI Extension)

Titan Hello converts the legacy Voice Chatbot extension into a **phone-answering AI receptionist** for tradies and field services.

## Milestones
- **Milestone A (this build):** Canonical extension restructure + stability fixes (no telephony yet).
- **Milestone B:** Twilio inbound calls + Media Streams ↔ ElevenLabs Agent realtime bridge + call flows + lead capture.

## Current Channels
- Admin UI (agents, training, conversation history)
- Public voice runtime API (kept for compatibility): `/api/v2/titan-hello/{uuid}`

## Next (Phone Answering)
- Add Twilio webhooks: `/api/v2/titan-hello/twilio/voice/*`
- Add call session tables and dashboard
- Add realtime audio bridge service

## Safety
- URL training uses safe HTTP fetching and blocks private/reserved IP ranges.
