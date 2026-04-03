# External-Chatbot Extension v6.1 - Complete Feature List

**Extension Version:** 6.1  
**Type:** External chatbot extension for MagicAI  
**Last Updated:** March 2026

---

## CORE CHATBOT FUNCTIONALITY

### Interaction Types
- **Automatic Response** - AI-powered automatic replies
- **Human Support** - Agent-based manual support
- **Smart Switch** - Hybrid mode (AI or Agent routing)

### AI Engine Support
- **OpenAI** - GPT-4 O and other models
- **Anthropic Claude** - Claude 3 Opus and variants
- **Google Gemini** - Gemini 3 Flash models
- **DeepSeek** - Custom integrations
- **X.AI** - Grok 2.1212 support
- **Multi-Model Routing** - Dynamic engine selection based on settings
- **Credit System** - Token-based credit tracking and consumption

### Message Handling
- **Text Messages** - Standard message exchange
- **Rich Message Types** - Multiple content format support
- **File Attachments** - Document and media uploads
- **Media Attachments** - Image, video, audio support
- **Internal Notes** - Private agent-only notes
- **Message Append** - Progressive message building
- **Message Export** - PDF conversation export

---

## CONVERSATION MANAGEMENT

### Conversation Features
- **Multi-Session Support** - Parallel conversation tracking
- **Session Management** - User session creation and persistence
- **Conversation History** - Complete message audit trail
- **Pinned Conversations** - Mark important conversations
- **Last Activity Tracking** - Monitor conversation freshness
- **Conversation Search** - Full-text and filtered search
- **Conversation Pagination** - Efficient large dataset handling
- **Conversation Status** - Ticket/escalation status tracking (Open, Closed, etc.)

### Customer Tracking
- **Customer Profiles** - CRM-style customer data storage
- **Country Code Support** - International customer tracking
- **Customer Favorites** - Flag important customers
- **Customer Sound Preferences** - Audio notification settings
- **Customer Email Collection** - Contact information gathering
- **Customer Segmentation** - Organize by behavior/attributes

---

## CUSTOMIZATION & UI/UX

### Visual Design
- **Logo Upload** - Custom branding (image/logo)
- **Avatar Selection** - 5 built-in avatars + custom uploads
- **Avatar Size Control** - Trigger bubble sizing options
- **Color Modes** - Light/Dark theme support
- **Custom Colors** - Brand color customization
- **Bubble Design Styles** - 6 design templates:
  - Blank
  - Plain
  - Links
  - Modern
  - Suggestions
  - Promo Banner

### Layout & Position
- **Trigger Position** - Left/Right side placement
- **Floating Bar** - Chat trigger customization
- **Welcome Banner** - Greeting message display
- **Header Background** - Image/color/gradient options
- **Welcome Background** - Custom onboarding visuals
- **Logo Display Toggle** - Show/hide branding
- **Date & Time Display** - Message timestamp visibility
- **Response Time Display** - Show average AI response metrics

### Interaction Controls
- **Pre-defined Questions** - Suggested quick replies
- **Suggested Prompts** - AI-generated prompt suggestions
- **Prompt Suggestions Toggle** - Enable/disable feature
- **Emoji Support** - Rich text formatting
- **Contact Form** - Integrated lead capture
- **Email Collection** - Explicit email gathering
- **Attachment Toggle** - Enable/disable file uploads
- **GDPR Compliance** - Privacy controls and data handling

---

## KNOWLEDGE BASE & TRAINING

### Training Methods
- **URL-based Training** - Scrape and learn from websites
- **PDF Training** - Extract knowledge from documents
- **Text Training** - Direct knowledge input
- **Q&A Training** - Question-answer pair learning
- **Excel/File Upload** - Bulk data import
- **Website Crawling** - Automatic content discovery

### Knowledge Management
- **Knowledge Base Articles** - Organized documentation
- **Article Publishing** - Content management interface
- **Article Linking** - Reference and cross-linking
- **Knowledge Base Search** - Searchable article database
- **Article Display** - User-facing article browser
- **Content Storage** - Rich content field support

### Embeddings & Semantic Search
- **OpenAI Embeddings** - Vector embedding support
- **Embedding Types** - Multiple embedding strategies
- **Embedding Deletion** - Remove obsolete knowledge
- **Embedding Generation** - Batch processing support
- **Vector Search** - Semantic similarity matching
- **Embedding Caching** - Performance optimization

---

## MULTI-CHANNEL INTEGRATION

### Channel Management
- **Channel Creation** - Multiple communication channels
- **Channel Types** - Different channel configurations
- **Webhook Support** - Outbound integration endpoints
- **Channel-Specific Routing** - Direct channel targeting
- **Conversation Channel Tracking** - Source attribution

### Social & Communication Links
- **WhatsApp Integration** - Direct messaging link
- **Telegram Integration** - Bot channel
- **Facebook Integration** - Messenger/page linking
- **Instagram Integration** - DM support
- **Social Links Display** - First-message social options
- **Product Tour Link** - Onboarding resources
- **Custom Links** - Flexible URL configurations

---

## AGENT & SUPPORT FEATURES

### Agent Support System
- **Human Agent Handoff** - Escalation to support team
- **Agent Conditions** - Smart routing rules
- **Canned Responses** - Pre-written quick replies
- **Canned Response Library** - Template management
- **Agent Status** - Availability tracking
- **Agent Replies** - Manual message handling
- **Support Connection** - Seamless handoff flow

### Ticket Management
- **Ticket Status Tracking** - Open/In Progress/Closed states
- **Ticket Assignment** - Agent assignment workflow
- **Email Notifications** - Alert system
- **Send Email At** - Scheduled email sending
- **Review Settings** - Post-interaction feedback
- **Review Collection** - Customer satisfaction ratings

---

## ADVANCED FEATURES

### Voice & Audio
- **Voice Call Enabled** - Voice conversation support (delegates to separate `chatbot-voice-call` extension)
- **Voice Call First Message** - Greeting message for voice interactions
- **Voice Call Provider** - Pluggable voice provider (external service integration)
- **Voice Call Voice ID** - Voice model/provider configuration
- **Voice Call Agent ID** - Agent assignment for voice calls
- **Voice Call Duration Tracking** - Records call duration in message history (seconds)
- **Voice Call Limits** - Per-plan duration caps (seconds limit per plan)
- **Voice Transcript Support** - Automatic transcription with `voice-transcript-user` and `voice-transcript-assistant` roles
- **Voice Call Event States** - `voice-call-started` and `voice-call-ended` message states
- **Sound Preferences** - Customer audio notification control toggle

### Analytics & Reporting
- **Conversation Analytics** - Stats and metrics
- **New Conversations Chart** - Trend visualization
- **Agent Replies Chart** - Agent workload tracking
- **Top Conversations List** - Popular threads
- **Page Visit Tracking** - User engagement metrics
- **Page Visit Recording** - Session analytics
- **Export Analytics** - Data download capability

### E-Commerce Features
- **E-Commerce Data** - Product catalog integration
- **Shopping Cart Support** - Cart abandonment recovery
- **Cart Tracking** - Purchase behavior analysis
- **Booking Assistant** - Calendar/appointment booking
- **Booking Conditions** - Availability rules

### Promotional Features
- **Promo Banner Support** - Marketing messages
- **Promo Banner Design** - Customizable layouts
- **Promotional Field Tracking** - Campaign attribution

### Security & Compliance
- **GDPR Fields** - Privacy consent tracking
- **GDPR Compliance** - Data protection controls
- **Trusted Domains** - Whitelist allowed sources
- **Domain Validation** - Security checks
- **Data Privacy** - Local-first data handling

---

## INFRASTRUCTURE & SYSTEM

### Database Architecture
**53+ Database Tables** including:
- `ext_chatbots` - Core chatbot configuration
- `ext_chatbot_conversations` - Conversation data
- `ext_chatbot_histories` - Message logs
- `ext_chatbot_customers` - Customer profiles
- `ext_chatbot_embeddings` - Vector storage
- `ext_chatbot_avatars` - Avatar library
- `ext_chatbot_channels` - Multi-channel config
- `ext_chatbot_knowledge_base_articles` - Article storage
- `ext_chatbot_canned_responses` - Template library
- `ext_chatbot_page_visits` - Analytics tracking
- `ext_chatbot_carts` - E-commerce carts
- And 40+ additional specialized tables

### API Endpoints

**Public API (v2)**
- `GET /api/v2/chatbot/{chatbot:uuid}` - Get chatbot config
- `GET /api/v2/chatbot/{uuid}/articles` - List knowledge articles
- `GET /api/v2/chatbot/{uuid}/articles/{id}/show` - Show article
- `GET/POST /api/v2/chatbot/{uuid}/session/{sessionId}/*` - Session management
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/conversation` - Create conversation
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/conversation/connect` - Connect to agent
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/conversation/{id}/messages` - Send message
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/conversation/{id}/file` - Upload file
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/conversation/{id}/review` - Submit review
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/send-email` - Email delivery
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/collect-email` - Collect email
- `POST /api/v2/chatbot/{uuid}/session/{sessionId}/page-visit` - Track page visit
- `PUT /api/v2/chatbot/{uuid}/session/{sessionId}/page-visit` - Leave page

**Admin Dashboard API**
- `GET/POST /dashboard/chatbot` - CRUD operations
- `GET /dashboard/chatbot/conversations` - List conversations
- `GET /dashboard/chatbot/conversations-with-paginate` - Paginated list
- `POST /dashboard/chatbot/conversations/search` - Search
- `GET/POST /dashboard/chatbot/train/*` - Training endpoints
- `GET/POST /dashboard/chatbot/analytics` - Analytics dashboard
- `GET/POST /dashboard/chatbot/knowledge-base-article` - KB management
- `GET/POST /dashboard/chatbot/canned-response` - Template management
- `GET/POST /dashboard/chatbot/chatbot-customer` - Customer management
- `GET/POST /dashboard/chatbot-multi-channel` - Channel management

### Frontend Framework
- **Alpine.js** - Lightweight reactive framework
- **Blade Templates** - Server-side templating
- **Tailwind CSS** - Utility-first styling
- **Custom SCSS** - Theme customization
- **JavaScript Components** - Custom chat UI elements

### Frontend UI Components
- **Floating Chat Bubble** - Trigger widget
- **Message Display** - Rich message rendering
- **Conversation List** - Multi-conversation view
- **Articles Browser** - Knowledge base UI
- **Contact Form** - Lead capture widget
- **Welcome Screen** - Greeting/onboarding
- **Thank You Page** - Confirmation screen
- **Header/Footer** - Navigation elements
- **Loader** - Loading indicators
- **Channel List** - Social/channel options
- **Conversation Form** - Message input

### Services & Business Logic
- `ChatbotService` - Core business operations
- `GeneratorService` - AI response generation with multi-engine support
- `TrainingService` - Knowledge ingestion
- `ChatbotAnalyticsService` - Metrics and reporting (11KB+ complex logic)
- `ConversationExportService` - PDF export
- `OpenAI MessageService` - AI message processing
- `OpenAI EmbeddingService` - Vector operations

### Parsers & Data Processing
- `PdfParser` - PDF content extraction
- `TextParser` - Plain text processing
- `LinkParser` - URL content scraping
- `ExcelParser` - Spreadsheet import
- `Multi-format Support` - Flexible data ingestion

### Tools & Utilities
- `KnowledgeBase Tool` - Knowledge retrieval and integration
- `ChatbotHelper` - Utility functions
- `Custom Enums` - Type-safe configurations

### Security & Access Control
- **Authorization Policies** - Chatbot, Article, and Canned Response policies
- **Role-Based Access** - User permission gating
- **Language Middleware** - Localization
- **Demo Mode Protection** - Demo-specific behaviors

### Configuration
- **Environment Variables** - `.env` configuration
- **Notification Settings** - Configurable alerts
- **Plan Limits** - Feature/usage tier management
- **Avatar Library** - 5 built-in + unlimited custom

### Asset Management
- **Avatar Storage** - PNG image library
- **Avatar Upload** - User custom avatars
- **Icon Sets** - SVG icon library
- **Image Assets** - Marketing/UI images
- **Public Asset Publishing** - CDN-friendly assets

---

## ADMIN/DASHBOARD FEATURES

- **Chatbot List View** - Overview of all chatbots
- **Chatbot Creation Wizard** - Multi-step setup
- **Conversation History Browser** - Detailed conversation review
- **Contact Information Panel** - Customer details sidebar
- **Chat History Tabs** - Organized message view
- **Filter & Sort** - Conversation filtering and sorting
- **Search Conversations** - Full-text search
- **Channel Filtering** - Filter by communication channel
- **Analytics Dashboard** - Real-time statistics
- **Training Interface** - Knowledge base management
- **Configuration Steps** - Multi-step edit wizard
- **Frontend Preview** - Live chatbot preview
- **Embed Code Generator** - Installation instructions

---

## DESIGN & CUSTOMIZATION OPTIONS

### Color & Theme
- Dark/Light mode toggle
- Custom primary color
- Custom trigger background
- Custom trigger foreground text
- Header background (solid, gradient, image)
- Welcome page background

### Bubble & Interaction Design
- 6 distinct bubble design templates
- Adjustable avatar sizing
- Position left/right
- Show/hide various UI elements
- Customizable welcome message
- Customizable connect message
- Custom welcome banner design

### Branding
- Logo upload and positioning
- Custom avatar per chatbot
- Footer link customization
- Privacy policy linking
- Terms of service linking
- Watch product tour linking

---

## SYSTEM REQUIREMENTS & DEPENDENCIES

- **Laravel Framework** - Latest (MagicAI v10 based)
- **PHP 8.1+** - Modern PHP support
- **Database** - PostgreSQL/MySQL with 53+ migrations
- **Authentication** - Laravel Auth middleware
- **API Support** - RESTful JSON API
- **Localization** - Multi-language support

---

## MIGRATION TIMELINE & FEATURE EVOLUTION

**Phase 1 (Oct 2024)** - Core foundation
- Basic chatbot, conversations, messages

**Phase 2 (Oct-Nov 2024)** - AI & Embeddings
- Embedding table, model/role tracking

**Phase 3 (Jan-Mar 2025)** - Multi-channel
- Channels, webhooks, agents

**Phase 4 (Jul-Aug 2025)** - CRM & Advanced
- Customer profiles, knowledge base, tickets

**Phase 5 (Aug-Oct 2025)** - Voice & Polish
- Voice calls, canned responses, styling

**Phase 6 (Nov-Dec 2025)** - Customization
- Bubble designs, background options

**Phase 7 (Feb-Mar 2026)** - E-Commerce & Social
- Cart support, social links, page visits, GDPR

---

## CLARIFICATIONS

### Voice Call Feature (NOT Inbound Phone Answering)

The "voice call" feature in External-Chatbot does **NOT** mean the chatbot can answer incoming phone calls. Instead:

- **What it is:** A voice conversation interface built into the chat widget that allows users to have voice interactions within the web/app context (like a voice memo or call-like experience)
- **How it works:** Delegates to a separate extension called `chatbot-voice-call` (not included in this package) which handles:
  - Voice input/output UI components
  - Speech-to-text transcription (user audio → AI input)
  - Text-to-speech response (AI output → user audio)
  - Call duration tracking
  - Voice provider integration (pluggable)
- **Data captured:** 
  - `voice_call_duration` - Seconds per call
  - `voice-transcript-user` / `voice-transcript-assistant` roles
  - Voice call event markers (`voice-call-started`, `voice-call-ended`)
- **Use case:** Allows users to "call" the chatbot using voice instead of typing, within the web/mobile interface
- **Not included:** Traditional phone system integration, IVR, inbound call answering, PSTN connectivity

---

- **Version:** 6.1
- **Database Tables:** 53+
- **API Endpoints:** 30+
- **UI Components:** 13+
- **AI Engines Supported:** 5+
- **Training Methods:** 6
- **Customization Options:** 50+
- **Lines of Code:** ~20,000+ (estimated)
- **Controllers:** 9
- **Models:** 11
- **Services:** 7
- **Enums:** 8
- **Middleware:** 1 specialized
