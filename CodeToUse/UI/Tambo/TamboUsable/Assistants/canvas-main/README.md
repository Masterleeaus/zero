# Canvas

**AI-powered Knowledge Base UI Kit** built on [Memvid](https://memvid.com) - the video-based memory format for AI.

Canvas provides drop-in React components and server utilities to build search, chat, and dashboard interfaces powered by your documents and data.

## Packages

| Package | Description | npm |
|---------|-------------|-----|
| `@memvid/canvas-core` | Core engine, memory client, LLM providers | [![npm](https://img.shields.io/npm/v/@memvid/canvas-core)](https://www.npmjs.com/package/@memvid/canvas-core) |
| `@memvid/canvas-react` | React components, templates, hooks | [![npm](https://img.shields.io/npm/v/@memvid/canvas-react)](https://www.npmjs.com/package/@memvid/canvas-react) |
| `@memvid/canvas-server` | Server utilities, Next.js route handlers | [![npm](https://img.shields.io/npm/v/@memvid/canvas-server)](https://www.npmjs.com/package/@memvid/canvas-server) |

## Quick Start

### 1. Install

```bash
npm install @memvid/canvas-react @memvid/canvas-server
# or
pnpm add @memvid/canvas-react @memvid/canvas-server
```

### 2. Create API Route (Next.js App Router)

```typescript
// app/api/canvas/[...path]/route.ts
import { createCanvasCatchAll } from '@memvid/canvas-server/next';
import { loadSettings, getApiKey, resolvePath } from '@memvid/canvas-core';

const basePath = process.cwd();

export const { GET, POST, OPTIONS } = createCanvasCatchAll({
  basePath,
  config: () => {
    const settings = loadSettings(basePath);
    const provider = settings?.llmProvider || 'openai';
    const memoryPath = resolvePath(settings?.memoryPath || './data/memory.mv2', basePath);

    return {
      llm: {
        provider,
        apiKey: getApiKey(provider) || '',
        model: settings?.llmModel,
      },
      memory: memoryPath,
    };
  },
});
```

### 3. Add the UI

```tsx
// app/page.tsx
'use client';

import { CanvasShell } from '@memvid/canvas-react/components';
import '@memvid/canvas-react/styles/canvas.css';

export default function Page() {
  return <CanvasShell apiBasePath="/api/canvas" />;
}
```

### 4. Add Environment Variables

```bash
# .env.local
OPENAI_API_KEY=sk-...
# or
ANTHROPIC_API_KEY=sk-ant-...
```

## Features

- **Search** - Semantic, lexical, and hybrid search across your documents
- **Chat** - RAG-powered chat with source citations
- **Dashboard** - Memory statistics and timeline view
- **File Upload** - Ingest PDFs, DOCX, XLSX, TXT, MD, JSON, and more
- **Dark/Light Mode** - Built-in theme support
- **Customizable** - Slots, hooks, and CSS variables for full control

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Your Next.js App                         │
├─────────────────────────────────────────────────────────────┤
│  @memvid/canvas-react                                        │
│  ├── CanvasShell (full app)                                  │
│  ├── Search, Chat, Dashboard templates                       │
│  └── useChat, useSearch hooks                                │
├─────────────────────────────────────────────────────────────┤
│  @memvid/canvas-server                                       │
│  ├── Next.js route handlers                                  │
│  └── File ingestion, search, chat endpoints                  │
├─────────────────────────────────────────────────────────────┤
│  @memvid/canvas-core                                         │
│  ├── CanvasEngine (orchestrates LLM + Memory)                │
│  ├── MemoryClient (wraps @memvid/sdk)                        │
│  └── LLM providers (OpenAI, Anthropic, Google)               │
├─────────────────────────────────────────────────────────────┤
│  @memvid/sdk                                                 │
│  └── .mv2 file format (video-based memory)                   │
└─────────────────────────────────────────────────────────────┘
```

## API Endpoints

The server package creates these endpoints:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/canvas/chat` | POST | Chat with memory context |
| `/api/canvas/search` | POST | Search documents |
| `/api/canvas/ingest` | POST | Upload and ingest files |
| `/api/canvas/memory` | GET | Memory info and stats |
| `/api/canvas/stats` | GET | Detailed statistics |
| `/api/canvas/settings` | GET/POST | Settings management |

## Supported File Types

Canvas can ingest:
- **Documents**: PDF, DOCX, DOC, TXT, MD
- **Spreadsheets**: XLSX, XLS, CSV
- **Presentations**: PPTX, PPT
- **Data**: JSON, XML, YAML

## Configuration

### Memory Path

```typescript
// Use absolute path for consistency
const memoryPath = path.join(process.cwd(), 'data', 'memory.mv2');
```

### LLM Providers

```typescript
{
  llm: {
    provider: 'openai',      // 'openai' | 'anthropic' | 'google'
    model: 'gpt-4o-mini',    // or 'claude-sonnet-4-20250514', etc.
    apiKey: process.env.OPENAI_API_KEY,
  }
}
```

### Theme Customization

```css
:root {
  --canvas-primary: #818cf8;
  --canvas-accent: #22c55e;
  --canvas-background: #09090b;
  --canvas-surface: #18181b;
  --canvas-border: #27272a;
  --canvas-text: #fafafa;
  --canvas-muted: #a1a1aa;
}
```

## Development

```bash
# Clone
git clone https://github.com/memvid/canvas.git
cd canvas

# Install
pnpm install

# Build all packages
pnpm build

# Run demo
cd memvid-memory-demo
pnpm dev
```

## Publishing

```bash
# Dry run (preview what would be published)
pnpm publish:dry

# Publish all packages to npm
pnpm publish:all
```

## Links

- [Memvid](https://memvid.com) - Video-based memory for AI
- [Documentation](https://docs.memvid.com/canvas)
- [npm: @memvid/canvas-core](https://www.npmjs.com/package/@memvid/canvas-core)
- [npm: @memvid/canvas-react](https://www.npmjs.com/package/@memvid/canvas-react)
- [npm: @memvid/canvas-server](https://www.npmjs.com/package/@memvid/canvas-server)

## License

MIT
