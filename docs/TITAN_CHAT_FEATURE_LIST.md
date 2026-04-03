# TITAN CHAT FEATURE LIST

**Generated:** 2026-04-03  
**Purpose:** Complete feature inventory of the merged conversational AI system.

---

## 1. Core Chat Features

### 1.1 Workspace Chat (AIChatPro)
- [x] Multi-conversation workspace with named chats
- [x] Chat history sidebar with search
- [x] Chat pinning
- [x] Chat title editing
- [x] Default screen modes: new | last | pinned
- [x] Guest chat support (no login required)
- [x] Chat categories / personas (OpenaiGeneratorChatCategory)
- [x] Premium plan gating per category
- [x] Multi-model support (Entity model selector)
- [x] Chatbot persona integration (Chatbot model)
- [x] Team-shared conversations
- [x] Chat export

### 1.2 Chat Execution (Canonical Runtime)
- [x] Token budget enforcement (TitanTokenBudget)
- [x] Memory recall before each AI decision (TitanMemoryService)
- [x] Memory storage after each AI decision
- [x] Nexus multi-core decision engine (ZeroCoreManager)
- [x] Consensus coordination (ConsensusCoordinator)
- [x] Critique loop refinement (CritiqueLoopEngine)
- [x] Authority weighting (AuthorityWeights)
- [x] Signal/Approval pipeline (SignalBridge)
- [x] Rewind support (RewindManager)
- [x] Activity telemetry (TitanCoreActivity events)

---

## 2. Memory Features

### 2.1 Session Memory
- [x] Per-session AI decision memory (tz_ai_memories table)
- [x] Memory snapshots (tz_ai_memory_snapshots)
- [x] Session handoff (SessionHandoffManager)
- [x] Vector memory embedding (optional, VectorMemoryAdapter)

### 2.2 User Memory (AiChatProMemory extension)
- [x] User-specific system prompt overrides per category
- [x] Guest memory by IP address
- [x] Admin default instructions per category
- [x] Instructions inheritance (user overrides admin)

### 2.3 Knowledge Memory
- [x] Knowledge base recall (KnowledgeManager)
- [x] Knowledge scope resolution per tenant (KnowledgeScopeResolver)
- [x] PDF training data ingestion
- [x] Web crawling training (LinkCrawler, ParserService)
- [x] Q&A training pairs
- [x] Text/Excel training data

---

## 3. AIChatPro Features

- [x] Advanced chat workspace UI
- [x] Folder/context organisation (AiChatProFolders)
- [x] File chat — chat over uploaded documents (AiChatProFileChat)
- [x] Memory/instructions panel (AiChatProMemory)
- [x] Multi-model switching
- [x] Streaming responses
- [x] Image generation tool (generate_image via AiChatProService)
- [x] Google search integration (Serper API)
- [x] Assistant integration (OpenAI Assistants API, FileSearchService)
- [x] Realtime voice chat (OpenaiRealtimeChat extension)
- [x] AWS Bedrock runtime support
- [x] OpenRouter support (OpenRouter extension)
- [x] Multi-model chat (MultiModel extension)
- [x] Temporary/anonymous chat (ChatProTempChat extension)
- [x] Chat sharing (ChatShare extension)
- [x] Chat settings (ChatSetting extension)

---

## 4. Canvas Features

- [x] Tiptap rich-text canvas workspace
- [x] Canvas document title saving
- [x] Canvas content persistence (UserTiptapContent)
- [x] Structured AI drafting (routes through TitanAIRouter)
- [x] Workflow drafting surface
- [x] Reasoning workspace
- [x] Context inspection
- [x] Conversation composition
- [x] Canvas settings page
- [x] Canvas button component (embed in other views)

---

## 5. Chatbot Features

### 5.1 Chatbot Configuration
- [x] Multiple chatbot personas (Chatbot model)
- [x] Custom role/instructions per bot
- [x] Custom avatar per bot
- [x] Configurable welcome message
- [x] AI model selection per bot
- [x] Widget dimensions configuration
- [x] Training data management (PDF, website, text, Q&A)

### 5.2 Chatbot Admin
- [x] AI model management (AiChatbotModelController)
- [x] Plan-based model gating
- [x] Chatbot status monitoring
- [x] External settings
- [x] Training data upload

### 5.3 Chatbot Widget
- [x] Embeddable chat widget (chatbot-embed.scss)
- [x] Widget coming-soon state
- [x] Public chatbot page

---

## 6. Channel Adapter Features

### 6.1 Facebook Messenger
- [x] Webhook receive (ChatbotMessengerWebhookController)
- [x] Message routing via TitanChatBridge
- [x] Messenger Send API response
- [x] Plan-based channel toggle

### 6.2 WhatsApp (Twilio)
- [x] Twilio webhook receive (ChatbotTwilioController)
- [x] Message routing via TitanChatBridge
- [x] Twilio message response
- [x] Conversation threading (TwilioConversationService)

### 6.3 Telegram
- [x] Telegram webhook receive
- [x] Message routing via TitanChatBridge
- [x] Telegram Bot API response

### 6.4 Voice
- [x] Voice chatbot configuration (ExtVoiceChatbot)
- [x] Voice avatars
- [x] Voice training data (ExtVoicechatbotTrain)
- [x] Conversation history (ExtVoicechatbotHistory)
- [x] ElevenLabs TTS synthesis
- [x] Speech-to-text transcript input
- [x] Realtime voice (OpenaiRealtimeChat)

### 6.5 Webchat
- [x] Embeddable webchat widget
- [x] Visitor session tracking
- [x] Page URL context

### 6.6 External Chatbot Embed
- [x] External embed token authentication
- [x] Origin URL validation
- [x] Visitor identity
- [x] Plan-based chatbot limit enforcement

---

## 7. Chatbot Agent / Inbox Features

- [x] Agent inbox (ChatbotAgent extension)
- [x] Unread message count (agent + AI bot)
- [x] Conversation list with filters
- [x] Message threads
- [x] Contact info panel (details + history)
- [x] Channel filter
- [x] Conversation sort
- [x] Ably real-time events (ChatbotForMenuEvent, ChatbotForPanelEvent)
- [x] New conversation real-time notification

---

## 8. Action Safety Features

- [x] Signal pipeline for all chat actions
- [x] Approval chain before execution
- [x] Rewind support (undo AI actions)
- [x] Audit trail (AuditTrail)
- [x] Budget enforcement before every AI call

---

## 9. Accessibility Features

- [x] Large-text safe UI structure (tiptap / canvas)
- [x] Keyboard navigation in chat UI
- [x] Voice-friendly interaction path (VoiceChannelAdapter)
- [x] Screen-reader compatible HTML structure in blade views
