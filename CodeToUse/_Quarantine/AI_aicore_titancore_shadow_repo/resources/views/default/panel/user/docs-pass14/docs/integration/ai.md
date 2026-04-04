# AI Integrations Documentation

## Overview

AIPlatform integrates with external AI models for enhanced functionality.

## Supported AI Systems

- **OpenAI GPT**: For chat and text generation.
- **Generative AI**: For image and media generation.
- **MCP**: For model context sharing.

## Setup

1. Install dependencies: `npm install openai`
2. Set API keys in .env: `OPENAI_API_KEY=your_key`
3. Configure endpoints.

## Usage

- Use bridges in `bridges/ai-bridges/` for interactions.
- Send prompts, receive responses.

## Security

- Protect API keys.
- Rate limiting for API calls.

## Troubleshooting

- Verify API keys.
- Check model availability.



---
## Titan Zero Federated Node Adjustment

This project now operates under a **device‑first federated node runtime model**:

• Devices act as primary execution nodes  
• Server coordinates validation, promotion, audit, and relay only  
• State changes must originate as signals before canonical promotion  
• Automation executes through Pulse handlers only  
• Corrections occur via Rewind — never destructive mutation  
• Tenant boundary enforced by `company_id`  
• Sync exchanges intent envelopes, not database state

Execution spine:

Device → Process → Signal → Validation → Canonical Event → Pulse → Rewind → Domain Update




---
## Titan Zero Signal-Orchestrated Runtime Layer

Runtime clarification for contributors:

• All state mutations MUST originate as signals
• Controllers should emit ProcessRecords, not direct writes
• Automation executes exclusively through Pulse handlers
• Corrections must route through Rewind workflows
• Devices act as primary execution nodes
• Server promotes canonical events only after validation
• Sync transports intent envelopes, never raw DB state

Recommended controller pattern:

Request → ProcessRecord → Signal → Validation → Event → Pulse → Domain Update



---
## Titan Zero Provider & Memory Isolation Model

Provider usage and memory access must follow the new node-first governance model.

Rules:

• Providers receive scoped context envelopes, never unrestricted tenant datasets
• BYO and tenant-scoped keys should remain isolated per company boundary
• Memory must attach to entities, lifecycle stages, or approved assistant scopes
• Cross-tenant retrieval is prohibited
• Node-local drafts, evidence, and short-term reasoning remain local until signal promotion requires transmission
• Assistants emit signals and reference scoped memory; they do not directly mutate entities

Preferred reasoning flow:

Node Context → Scoped Memory → Provider Envelope → Signal Proposal → Validation → Canonical Event
