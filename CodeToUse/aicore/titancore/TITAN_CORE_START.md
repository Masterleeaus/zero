# Titan Core Start

This pass starts the host-native Titan Zero core inside the current `zero-main` site.

## What was added

- `app/TitanCore/*` service destination tree
- `TitanCoreServiceProvider`
- `config/titan_core.php`
- module registry and source catalog
- core managers for Zero, Pulse, Omni, and Agent Studio
- bridges to existing Titan Signal process/signal services

## Intent

This is not the final core extraction. It is the host-safe foundation so the next passes can merge code from:

- Titan AI residual core
- AICores bundle
- AiSocialMedia
- SocialMediaAgent
- External-Chatbot
- AIChatPro
- ChatbotVoice

without duplicating CRM, Work, Money, auth, or existing Signal/Rewind ownership.

## Next merge targets

1. Zero AI runtime + context compiler
2. Shared knowledge and memory extraction
3. Pulse automation extraction
4. Agent Studio extraction
5. Omni conversation and voice extraction
