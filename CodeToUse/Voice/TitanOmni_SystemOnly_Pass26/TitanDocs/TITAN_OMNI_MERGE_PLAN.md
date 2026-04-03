# Titan Omni Merge Plan

## Canonical base
- Keep `app/Extensions/Chatbot` as the primary existing chatbot extension.
- Keep `ChatbotVoice`, `ChatbotWhatsapp`, `ChatbotMessenger`, `ChatbotTelegram`, and `ElevenlabsVoiceChat` as separate channel/runtime extensions.
- Add Omni as shared core under `/app/Models/Omni`, `/app/Services/Omni`, and `/app/Http/Controllers/Omni`.

## Merge order
1. Create Omni schema and models.
2. Add dual-write from existing extensions into Omni tables.
3. Switch unified reads for conversation history.
4. Move knowledge retrieval into OmniKnowledgeService.
5. Point internal desk/chat surfaces to Omni conversations.
6. Reduce channel extensions to transport/webhook adapters over time.

## Non-destructive rule
Do not delete extension tables first.
Do not collapse all channels into one extension.
Use Omni as the shared data and orchestration core.
