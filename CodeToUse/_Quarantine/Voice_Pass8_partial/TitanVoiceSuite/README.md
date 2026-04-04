# Titan Voice Suite

Merged pass 1 foundation for the uploaded chatbot stack.

## Included packages
- `ChatbotWhatsapp/` — unified WhatsApp + SMS + Voice transport layer
- `ChatbotVoice/` — external voice chatbot builder
- `ElevenlabsVoiceChat/` — ElevenLabs runtime and training layer
- `TitanDocs/` — implementation docs and references

## What changed in this pass
- merged Twilio SMS + voice transport files into `ChatbotWhatsapp`
- added unified communication config
- added voice call lifecycle routes
- upgraded webhook controller for channel auto-detection
- upgraded conversation service for WhatsApp, SMS, and voice handling
- added migration for SMS/voice support and call logs
- cleaned obvious metadata junk by rebuilding a fresh suite folder
- fixed `ext_voicechatbot_trains` migration rollback in `ChatbotVoice`
- added cleanup aliases for `ChatbotVoiceService` and `ChatbotVoiceEmbedController`

## Primary install targets
- `app/Extensions/ChatbotWhatsapp/`
- `app/Extensions/ChatbotVoice/`
- `app/Extensions/ElevenLabsVoiceChat/`

## First validation checklist
1. register the three service providers
2. publish/install the extension folders
3. run migrations
4. verify Twilio credentials in chatbot channel records
5. hit the unified webhook route
6. test WhatsApp, SMS, and voice callback paths

## Notes
This pass focuses on **Phase 0 + Phase 1 foundation**: base consolidation, unified routes, SMS/voice transport, and shared conversation plumbing.
