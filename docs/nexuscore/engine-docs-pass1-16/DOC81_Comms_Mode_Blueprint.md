# Comms Mode Blueprint

## Scope
Unified conversation layer across channels.

## Entities
Conversation → Message → Channel → Participant → Attachment → Intent

## Responsibilities
- Inbox aggregation
- Channel bridging
- Assistant routing
- Notification orchestration
- Conversation tagging
- Escalation handling

## Signals
message.received
message.sent
conversation.escalated
assistant.intervened
