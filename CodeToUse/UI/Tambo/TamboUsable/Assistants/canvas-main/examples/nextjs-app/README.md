# Canvas Next.js Example

A complete example of using Canvas with Next.js App Router.

## Setup

1. Install dependencies:

```bash
pnpm install
```

2. Create `.env.local`:

```env
# Required - your LLM API key
ANTHROPIC_API_KEY=sk-ant-...

# Optional - for embeddings (defaults to same as LLM)
OPENAI_API_KEY=sk-...

# Optional - for cloud memory sync
MEMVID_API_KEY=...
```

3. Run the development server:

```bash
pnpm dev
```

## Project Structure

```
├── app/
│   ├── api/
│   │   └── chat/
│   │       └── route.ts    # Chat API endpoint
│   ├── layout.tsx          # Root layout
│   └── page.tsx            # Chat page
├── lib/
│   └── canvas.ts           # Canvas configuration
├── components/
│   └── Chat.tsx            # Chat component
└── data/
    └── memory.mv2          # Memory file (auto-created)
```

## Code Examples

### lib/canvas.ts

```typescript
import { createCanvasHandlers } from '@memvid/canvas-server/next';

export const canvasHandlers = createCanvasHandlers({
  config: {
    llm: {
      provider: 'anthropic',
      apiKey: process.env.ANTHROPIC_API_KEY!,
    },
    embedding: {
      provider: 'openai',
      apiKey: process.env.OPENAI_API_KEY!,
    },
    memory: './data/memory.mv2',
  },
  cors: true,
});
```

### app/api/chat/route.ts

```typescript
import { canvasHandlers } from '@/lib/canvas';

export const { POST, OPTIONS } = canvasHandlers.chat;
```

### app/page.tsx

```tsx
'use client';

import { Canvas, Chat } from '@memvid/canvas-react';
import '@memvid/canvas-react/styles';

export default function HomePage() {
  return (
    <Canvas.Provider
      llm={{ provider: 'anthropic', apiKey: '' }} // Empty - server handles this
      memory="./memory.mv2" // Path doesn't matter on client
    >
      <main className="h-screen">
        <Chat
          header={<h1 className="text-xl font-semibold">AI Assistant</h1>}
          placeholder="Type your message..."
          streaming={true}
        />
      </main>
    </Canvas.Provider>
  );
}
```

## Features Demonstrated

- **Streaming responses** - Real-time token streaming
- **Memory context** - Automatic retrieval from .mv2 file
- **Error handling** - Graceful error display
- **CORS support** - For API requests
- **Theme support** - Light/dark mode

## Customization

### Custom Message Rendering

```tsx
<Chat
  renderContent={(content, message) => (
    <ReactMarkdown>{content}</ReactMarkdown>
  )}
/>
```

### Custom Avatars

```tsx
<Chat
  userAvatar={<UserAvatar />}
  assistantAvatar={<BotAvatar />}
/>
```

### Access Chat State Directly

```tsx
import { useChat } from '@memvid/canvas-react/hooks';

function CustomChat() {
  const { messages, send, isStreaming } = useChat();

  // Build your own UI
}
```
