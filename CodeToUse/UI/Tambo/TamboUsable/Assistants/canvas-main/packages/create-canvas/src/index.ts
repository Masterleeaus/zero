/**
 * create-memvid-canvas
 *
 * CLI tool to scaffold or add Memvid Canvas to any project.
 *
 * Usage:
 *   npx create-memvid-canvas my-app          # New Next.js project
 *   npx create-memvid-canvas .               # Add to current directory
 *   npx create-memvid-canvas --api-only      # Node.js API only
 */

import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';
import prompts from 'prompts';
import pc from 'picocolors';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// ============================================================================
// Project Detection
// ============================================================================

interface ProjectInfo {
  type: 'new' | 'nextjs' | 'nodejs' | 'unknown';
  hasAppDir: boolean;
  hasPagesDir: boolean;
  hasSrcDir: boolean;
  hasPackageJson: boolean;
  hasConflictingRoutes: boolean;
  packageManager: 'npm' | 'pnpm' | 'yarn' | 'bun';
}

function detectProject(dir: string): ProjectInfo {
  const info: ProjectInfo = {
    type: 'new',
    hasAppDir: false,
    hasPagesDir: false,
    hasSrcDir: false,
    hasPackageJson: false,
    hasConflictingRoutes: false,
    packageManager: 'npm',
  };

  // Check for package.json
  const packageJsonPath = path.join(dir, 'package.json');
  if (fs.existsSync(packageJsonPath)) {
    info.hasPackageJson = true;
    try {
      const pkg = JSON.parse(fs.readFileSync(packageJsonPath, 'utf-8'));
      const deps = { ...pkg.dependencies, ...pkg.devDependencies };

      if (deps['next']) {
        info.type = 'nextjs';
      } else if (deps['express'] || deps['fastify'] || deps['hono'] || deps['koa']) {
        info.type = 'nodejs';
      } else {
        info.type = 'unknown';
      }
    } catch {
      info.type = 'unknown';
    }
  }

  // Check directory structure
  info.hasSrcDir = fs.existsSync(path.join(dir, 'src'));
  info.hasAppDir = fs.existsSync(path.join(dir, 'app')) || fs.existsSync(path.join(dir, 'src', 'app'));
  info.hasPagesDir = fs.existsSync(path.join(dir, 'pages')) || fs.existsSync(path.join(dir, 'src', 'pages'));

  // Check for conflicting routes
  const appDir = info.hasSrcDir ? path.join(dir, 'src', 'app') : path.join(dir, 'app');
  if (fs.existsSync(appDir)) {
    const catchAllPath = path.join(appDir, '[[...path]]');
    const catchAllSlugPath = path.join(appDir, '[[...slug]]');
    info.hasConflictingRoutes = fs.existsSync(catchAllPath) || fs.existsSync(catchAllSlugPath);
  }

  // Detect package manager
  if (fs.existsSync(path.join(dir, 'pnpm-lock.yaml'))) {
    info.packageManager = 'pnpm';
  } else if (fs.existsSync(path.join(dir, 'yarn.lock'))) {
    info.packageManager = 'yarn';
  } else if (fs.existsSync(path.join(dir, 'bun.lockb'))) {
    info.packageManager = 'bun';
  }

  return info;
}

// ============================================================================
// Templates
// ============================================================================

const NEXTJS_PAGE_TEMPLATE = `'use client';

/**
 * Canvas App - Handles all Canvas routes
 *
 * Routes:
 * - /canvas (or your base path)
 * - /canvas/setup
 * - /canvas/settings
 */

import { useRouter, usePathname } from 'next/navigation';
import { CanvasProvider, CanvasShell, useCanvas } from '@memvid/canvas-react/components';

function CanvasApp() {
  const router = useRouter();
  const { settings, config } = useCanvas();

  return (
    <CanvasShell
      settings={settings}
      config={config}
      onNavigate={(path) => router.push(path)}
    />
  );
}

export default function CanvasPage() {
  const router = useRouter();
  const pathname = usePathname();

  // Extract the canvas-relative path
  const basePath = '/canvas';
  const canvasPath = pathname.startsWith(basePath)
    ? pathname.slice(basePath.length) || '/'
    : pathname;

  return (
    <CanvasProvider
      pathname={canvasPath}
      setupPath="/setup"
      settingsPath="/settings"
      onNavigate={(path) => {
        // Navigate within canvas base path
        const fullPath = path === '/' ? basePath : basePath + path;
        router.push(fullPath);
      }}
    >
      <CanvasApp />
    </CanvasProvider>
  );
}
`;

const NEXTJS_ROOT_PAGE_TEMPLATE = `'use client';

/**
 * Canvas App - Single Page Handler
 *
 * Handles all routes:
 * - / (home)
 * - /setup (setup wizard)
 * - /settings (settings panel)
 */

import { useRouter, usePathname } from 'next/navigation';
import { CanvasProvider, CanvasShell, useCanvas } from '@memvid/canvas-react/components';

function CanvasApp() {
  const router = useRouter();
  const { settings, config } = useCanvas();

  return (
    <CanvasShell
      settings={settings}
      config={config}
      onNavigate={(path) => router.push(path)}
    />
  );
}

export default function Page() {
  const router = useRouter();
  const pathname = usePathname();

  return (
    <CanvasProvider
      pathname={pathname}
      onNavigate={(path) => router.push(path)}
    >
      <CanvasApp />
    </CanvasProvider>
  );
}
`;

const NEXTJS_API_TEMPLATE = `/**
 * Canvas API - Catch-All Route
 *
 * Handles all Canvas API endpoints:
 * - /api/canvas/memory
 * - /api/canvas/search
 * - /api/canvas/chat
 * - /api/canvas/stats
 * - /api/canvas/asset
 * - /api/canvas/ingest
 * - /api/canvas/settings
 * - /api/canvas/create-memory
 */

import { createCanvasCatchAll } from '@memvid/canvas-server/next';
import { loadSettings, getApiKey } from '@memvid/canvas-core';

export const { GET, POST, OPTIONS } = createCanvasCatchAll({
  basePath: process.cwd(),
  config: () => {
    const settings = loadSettings();
    const provider = settings?.llmProvider || 'openai';

    return {
      llm: {
        provider,
        apiKey: getApiKey(provider) || '',
        model: settings?.llmModel,
      },
      memory: settings?.memoryPath || './data/memory.mv2',
    };
  },
});
`;

const NODEJS_EXPRESS_TEMPLATE = `/**
 * Canvas API Server (Express)
 *
 * Standalone API server for Memvid Canvas.
 * Can be integrated into existing Express apps.
 *
 * Usage:
 *   node canvas-server.js
 *   # or import and use the router
 */

import express from 'express';
import cors from 'cors';
import { createCanvasRouter } from '@memvid/canvas-server/express';
import { loadSettings, getApiKey } from '@memvid/canvas-core';

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Canvas API router
const canvasRouter = createCanvasRouter({
  basePath: process.cwd(),
  config: () => {
    const settings = loadSettings();
    const provider = settings?.llmProvider || 'openai';

    return {
      llm: {
        provider,
        apiKey: getApiKey(provider) || '',
        model: settings?.llmModel,
      },
      memory: settings?.memoryPath || './data/memory.mv2',
    };
  },
});

// Mount Canvas routes at /api/canvas
app.use('/api/canvas', canvasRouter);

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok' });
});

app.listen(PORT, () => {
  console.log(\`Canvas API server running on http://localhost:\${PORT}\`);
  console.log(\`API endpoints available at http://localhost:\${PORT}/api/canvas/*\`);
});

export { canvasRouter };
`;

const NODEJS_STANDALONE_TEMPLATE = `/**
 * Canvas API Server (Standalone)
 *
 * Lightweight standalone server using native Node.js http.
 * For use in Node.js projects without Express.
 *
 * Usage:
 *   node canvas-server.mjs
 */

import { createServer } from 'http';
import { createCanvasHandler } from '@memvid/canvas-server/node';
import { loadSettings, getApiKey } from '@memvid/canvas-core';

const PORT = process.env.PORT || 3001;

const handler = createCanvasHandler({
  basePath: process.cwd(),
  config: () => {
    const settings = loadSettings();
    const provider = settings?.llmProvider || 'openai';

    return {
      llm: {
        provider,
        apiKey: getApiKey(provider) || '',
        model: settings?.llmModel,
      },
      memory: settings?.memoryPath || './data/memory.mv2',
    };
  },
});

const server = createServer(async (req, res) => {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    res.writeHead(204);
    res.end();
    return;
  }

  // Route to Canvas handler
  if (req.url?.startsWith('/api/canvas')) {
    return handler(req, res);
  }

  // Health check
  if (req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'ok' }));
    return;
  }

  // 404
  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ error: 'Not found' }));
});

server.listen(PORT, () => {
  console.log(\`Canvas API server running on http://localhost:\${PORT}\`);
  console.log(\`API endpoints available at http://localhost:\${PORT}/api/canvas/*\`);
});
`;

const LAYOUT_TEMPLATE = `import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'Canvas',
  description: 'AI-powered knowledge base',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
`;

const GLOBALS_CSS_TEMPLATE = `@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
  --background: #09090b;
  --foreground: #fafafa;
}

body {
  background: var(--background);
  color: var(--foreground);
  font-family: system-ui, sans-serif;
}
`;

const ENV_TEMPLATE = `# LLM Provider API Keys (set one)
OPENAI_API_KEY=sk-...
# ANTHROPIC_API_KEY=sk-ant-...

# Memvid API Key (for embeddings)
# MEMVID_API_KEY=mv-...
`;

// ============================================================================
// Canvas Config Template
// ============================================================================

interface CanvasConfigOptions {
  brandName: string;
  tagline: string;
  theme: 'dark' | 'light' | 'system';
  primaryColor: string;
  features: {
    search: boolean;
    chat: boolean;
    dashboard: boolean;
  };
  llmProvider: 'openai' | 'anthropic';
}

function createCanvasConfig(options: CanvasConfigOptions): string {
  const themePresets: Record<string, { primary: string; accent: string }> = {
    '#818cf8': { primary: '#818cf8', accent: '#22c55e' }, // Indigo (default)
    '#3b82f6': { primary: '#3b82f6', accent: '#06b6d4' }, // Blue
    '#10b981': { primary: '#10b981', accent: '#6366f1' }, // Emerald
    '#f59e0b': { primary: '#f59e0b', accent: '#ef4444' }, // Amber
    '#ec4899': { primary: '#ec4899', accent: '#8b5cf6' }, // Pink
  };

  const colors = themePresets[options.primaryColor] || themePresets['#818cf8'];

  return `/**
 * Canvas Configuration
 *
 * This file controls all aspects of your Canvas app.
 * Edit this file to customize branding, features, and behavior.
 *
 * @see https://github.com/memvid-org/canvas#configuration
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  // === BRAND ===
  brand: {
    name: '${options.brandName}',
    tagline: '${options.tagline}',
    // logo: '/logo.svg',      // Add your logo
    // favicon: '/favicon.ico', // Add your favicon
  },

  // === THEME ===
  theme: {
    mode: '${options.theme}',
    colors: {
      primary: '${colors.primary}',
      accent: '${colors.accent}',
    },
    // fonts: {
    //   display: 'Inter',
    //   body: 'Inter',
    //   mono: 'JetBrains Mono',
    // },
    radius: 'md',
  },

  // === FEATURES ===
  features: {
    search: {
      enabled: ${options.features.search},
      modes: ['semantic', 'lexical', 'hybrid'],
      defaultMode: 'hybrid',
    },
    chat: {
      enabled: ${options.features.chat},
      welcomeMessage: 'How can I help you today?',
      showSources: true,
    },
    dashboard: {
      enabled: ${options.features.dashboard},
    },
  },

  // === LLM ===
  llm: {
    provider: '${options.llmProvider}',
    // model: 'gpt-4o', // Optional: specify model
  },

  // === MEMORY ===
  memory: {
    path: './data/memory.mv2',
  },

  // === API ===
  api: {
    basePath: '/api/canvas',
  },
});
`;
}

const GITIGNORE_TEMPLATE = `# Dependencies
node_modules/
.pnpm-store/

# Next.js
.next/
out/

# Build
dist/
build/

# Environment
.env
.env.local
.env*.local

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Data
data/
*.mv2
`;

// ============================================================================
// File Creation Helpers
// ============================================================================

function createFile(filePath: string, content: string) {
  const dir = path.dirname(filePath);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
  fs.writeFileSync(filePath, content);
}

function createPackageJson(name: string, type: 'nextjs' | 'nodejs') {
  if (type === 'nextjs') {
    return JSON.stringify({
      name,
      version: '0.1.0',
      private: true,
      scripts: {
        dev: 'next dev',
        build: 'next build',
        start: 'next start',
        lint: 'next lint',
      },
      dependencies: {
        '@memvid/canvas-core': '^0.1.0',
        '@memvid/canvas-react': '^0.1.0',
        '@memvid/canvas-server': '^0.1.0',
        next: '^15.0.0',
        react: '^19.0.0',
        'react-dom': '^19.0.0',
      },
      devDependencies: {
        '@types/node': '^20.0.0',
        '@types/react': '^19.0.0',
        '@types/react-dom': '^19.0.0',
        autoprefixer: '^10.0.0',
        postcss: '^8.0.0',
        tailwindcss: '^3.0.0',
        typescript: '^5.0.0',
      },
    }, null, 2);
  }

  return JSON.stringify({
    name,
    version: '0.1.0',
    private: true,
    type: 'module',
    scripts: {
      start: 'node canvas-server.mjs',
      dev: 'node --watch canvas-server.mjs',
    },
    dependencies: {
      '@memvid/canvas-core': '^0.1.0',
      '@memvid/canvas-server': '^0.1.0',
    },
  }, null, 2);
}

function mergePackageJson(existingPath: string, newDeps: Record<string, string>, newDevDeps: Record<string, string> = {}) {
  const existing = JSON.parse(fs.readFileSync(existingPath, 'utf-8'));

  existing.dependencies = {
    ...existing.dependencies,
    ...newDeps,
  };

  if (Object.keys(newDevDeps).length > 0) {
    existing.devDependencies = {
      ...existing.devDependencies,
      ...newDevDeps,
    };
  }

  fs.writeFileSync(existingPath, JSON.stringify(existing, null, 2));
}

// ============================================================================
// Setup Modes
// ============================================================================

type SetupMode = 'full' | 'add-routes' | 'api-only';
type TemplateType = 'minimal' | 'full' | 'custom';

interface SetupOptions {
  mode: SetupMode;
  template: TemplateType;
  projectDir: string;
  projectInfo: ProjectInfo;
  basePath: string; // For add-routes mode, e.g., '/canvas'
  configOptions?: CanvasConfigOptions;
}

// ============================================================================
// Interactive Configuration Wizard
// ============================================================================

async function runConfigWizard(defaults: Partial<CanvasConfigOptions> = {}): Promise<CanvasConfigOptions> {
  console.log();
  console.log(pc.bold(pc.cyan('  Configure your Canvas app')));
  console.log(pc.dim('  (Press Enter to use defaults)'));
  console.log();

  const response = await prompts([
    {
      type: 'text',
      name: 'brandName',
      message: 'App name:',
      initial: defaults.brandName || 'My Knowledge Base',
    },
    {
      type: 'text',
      name: 'tagline',
      message: 'Tagline:',
      initial: defaults.tagline || 'AI-powered search and chat',
    },
    {
      type: 'select',
      name: 'theme',
      message: 'Theme:',
      choices: [
        { title: '🌙 Dark (recommended)', value: 'dark' },
        { title: '☀️  Light', value: 'light' },
        { title: '💻 System', value: 'system' },
      ],
      initial: 0,
    },
    {
      type: 'select',
      name: 'primaryColor',
      message: 'Primary color:',
      choices: [
        { title: '💜 Indigo', value: '#818cf8' },
        { title: '💙 Blue', value: '#3b82f6' },
        { title: '💚 Emerald', value: '#10b981' },
        { title: '🧡 Amber', value: '#f59e0b' },
        { title: '💗 Pink', value: '#ec4899' },
      ],
      initial: 0,
    },
    {
      type: 'multiselect',
      name: 'features',
      message: 'Features to enable:',
      choices: [
        { title: '🔍 Search', value: 'search', selected: true },
        { title: '💬 Chat', value: 'chat', selected: true },
        { title: '📊 Dashboard', value: 'dashboard', selected: true },
      ],
      hint: '- Space to select. Return to submit',
    },
    {
      type: 'select',
      name: 'llmProvider',
      message: 'LLM Provider:',
      choices: [
        { title: 'OpenAI (GPT-4)', value: 'openai' },
        { title: 'Anthropic (Claude)', value: 'anthropic' },
      ],
      initial: 0,
    },
  ]);

  // Handle cancellation
  if (!response.brandName) {
    console.log(pc.red('Cancelled.'));
    process.exit(1);
  }

  // Convert features array to object
  const featuresArray = response.features || ['search', 'chat', 'dashboard'];

  return {
    brandName: response.brandName,
    tagline: response.tagline,
    theme: response.theme || 'dark',
    primaryColor: response.primaryColor || '#818cf8',
    features: {
      search: featuresArray.includes('search'),
      chat: featuresArray.includes('chat'),
      dashboard: featuresArray.includes('dashboard'),
    },
    llmProvider: response.llmProvider || 'openai',
  };
}

// Quick setup with defaults
function getDefaultConfig(): CanvasConfigOptions {
  return {
    brandName: 'My Knowledge Base',
    tagline: 'AI-powered search and chat',
    theme: 'dark',
    primaryColor: '#818cf8',
    features: {
      search: true,
      chat: true,
      dashboard: true,
    },
    llmProvider: 'openai',
  };
}

async function setupFullNextJs(options: SetupOptions) {
  const { projectDir, configOptions } = options;
  const srcDir = path.join(projectDir, 'src');

  // Generate canvas.config.ts if we have config options
  const configContent = configOptions
    ? createCanvasConfig(configOptions)
    : createCanvasConfig(getDefaultConfig());

  // Create all files
  const files: Record<string, string> = {
    'canvas.config.ts': configContent,
    'src/app/[[...path]]/page.tsx': NEXTJS_ROOT_PAGE_TEMPLATE,
    'src/app/api/canvas/[...path]/route.ts': NEXTJS_API_TEMPLATE,
    'src/app/layout.tsx': LAYOUT_TEMPLATE,
    'src/app/globals.css': GLOBALS_CSS_TEMPLATE,
    'next.config.ts': `import type { NextConfig } from 'next';

const nextConfig: NextConfig = {};

export default nextConfig;
`,
    'tailwind.config.ts': `import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/**/*.{js,ts,jsx,tsx,mdx}',
    './node_modules/@memvid/canvas-react/**/*.{js,ts,jsx,tsx}',
  ],
  theme: { extend: {} },
  plugins: [],
};

export default config;
`,
    'postcss.config.mjs': `const config = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
};

export default config;
`,
    'tsconfig.json': JSON.stringify({
      compilerOptions: {
        target: 'ES2017',
        lib: ['dom', 'dom.iterable', 'esnext'],
        allowJs: true,
        skipLibCheck: true,
        strict: true,
        noEmit: true,
        esModuleInterop: true,
        module: 'esnext',
        moduleResolution: 'bundler',
        resolveJsonModule: true,
        isolatedModules: true,
        jsx: 'preserve',
        incremental: true,
        plugins: [{ name: 'next' }],
        paths: { '@/*': ['./src/*'] },
      },
      include: ['next-env.d.ts', '**/*.ts', '**/*.tsx', '.next/types/**/*.ts'],
      exclude: ['node_modules'],
    }, null, 2),
    '.env.example': ENV_TEMPLATE,
    '.gitignore': GITIGNORE_TEMPLATE,
  };

  for (const [filePath, content] of Object.entries(files)) {
    createFile(path.join(projectDir, filePath), content);
    console.log(pc.green('  +') + pc.dim(` ${filePath}`));
  }

  // Create package.json
  const packageJsonPath = path.join(projectDir, 'package.json');
  const packageName = path.basename(projectDir);
  fs.writeFileSync(packageJsonPath, createPackageJson(packageName, 'nextjs'));
  console.log(pc.green('  +') + pc.dim(' package.json'));
}

async function setupAddRoutes(options: SetupOptions) {
  const { projectDir, projectInfo, basePath, configOptions } = options;
  const srcPrefix = projectInfo.hasSrcDir ? 'src/' : '';

  // Use a sub-path to avoid conflicts
  const routePath = basePath.replace(/^\//, ''); // 'canvas' from '/canvas'

  // Generate canvas.config.ts if we have config options
  const configContent = configOptions
    ? createCanvasConfig(configOptions)
    : createCanvasConfig(getDefaultConfig());

  const files: Record<string, string> = {
    'canvas.config.ts': configContent,
    [`${srcPrefix}app/${routePath}/[[...path]]/page.tsx`]: NEXTJS_PAGE_TEMPLATE.replace(
      "const basePath = '/canvas'",
      `const basePath = '/${routePath}'`
    ),
    [`${srcPrefix}app/api/canvas/[...path]/route.ts`]: NEXTJS_API_TEMPLATE,
  };

  for (const [filePath, content] of Object.entries(files)) {
    const fullPath = path.join(projectDir, filePath);
    if (fs.existsSync(fullPath)) {
      console.log(pc.yellow('  ~') + pc.dim(` ${filePath} (skipped - already exists)`));
    } else {
      createFile(fullPath, content);
      console.log(pc.green('  +') + pc.dim(` ${filePath}`));
    }
  }

  // Create .env.example if it doesn't exist
  const envPath = path.join(projectDir, '.env.example');
  if (!fs.existsSync(envPath)) {
    createFile(envPath, ENV_TEMPLATE);
    console.log(pc.green('  +') + pc.dim(' .env.example'));
  }

  // Merge dependencies into existing package.json
  const packageJsonPath = path.join(projectDir, 'package.json');
  if (fs.existsSync(packageJsonPath)) {
    mergePackageJson(packageJsonPath, {
      '@memvid/canvas-core': '^0.1.0',
      '@memvid/canvas-react': '^0.1.0',
      '@memvid/canvas-server': '^0.1.0',
    });
    console.log(pc.green('  ~') + pc.dim(' package.json (updated dependencies)'));
  }
}

async function setupApiOnly(options: SetupOptions) {
  const { projectDir, projectInfo } = options;

  // For existing Node.js projects, add the server file
  const serverFile = projectInfo.hasPackageJson
    ? 'canvas-api.mjs'
    : 'canvas-server.mjs';

  const files: Record<string, string> = {
    [serverFile]: NODEJS_STANDALONE_TEMPLATE,
    '.env.example': ENV_TEMPLATE,
  };

  for (const [filePath, content] of Object.entries(files)) {
    const fullPath = path.join(projectDir, filePath);
    if (fs.existsSync(fullPath)) {
      console.log(pc.yellow('  ~') + pc.dim(` ${filePath} (skipped - already exists)`));
    } else {
      createFile(fullPath, content);
      console.log(pc.green('  +') + pc.dim(` ${filePath}`));
    }
  }

  // If no package.json, create one
  if (!projectInfo.hasPackageJson) {
    const packageJsonPath = path.join(projectDir, 'package.json');
    const packageName = path.basename(projectDir);
    fs.writeFileSync(packageJsonPath, createPackageJson(packageName, 'nodejs'));
    console.log(pc.green('  +') + pc.dim(' package.json'));
  } else {
    // Merge dependencies
    const packageJsonPath = path.join(projectDir, 'package.json');
    mergePackageJson(packageJsonPath, {
      '@memvid/canvas-core': '^0.1.0',
      '@memvid/canvas-server': '^0.1.0',
    });
    console.log(pc.green('  ~') + pc.dim(' package.json (updated dependencies)'));
  }
}

// ============================================================================
// Main CLI
// ============================================================================

async function main() {
  console.log();
  console.log(pc.bold(pc.cyan('  create-memvid-canvas')));
  console.log(pc.dim('  Add AI-powered memory to any project'));
  console.log();

  // Parse args
  const args = process.argv.slice(2);
  const isApiOnly = args.includes('--api-only');
  const targetArg = args.find(a => !a.startsWith('-'));

  // Determine project directory
  let projectDir: string;
  let isNewProject = false;

  if (!targetArg || targetArg === '.') {
    projectDir = process.cwd();
  } else {
    projectDir = path.resolve(process.cwd(), targetArg);
    isNewProject = !fs.existsSync(projectDir);
  }

  // Detect existing project
  const projectInfo = isNewProject
    ? { type: 'new' as const, hasAppDir: false, hasPagesDir: false, hasSrcDir: false, hasPackageJson: false, hasConflictingRoutes: false, packageManager: 'npm' as const }
    : detectProject(projectDir);

  // Determine setup mode
  let mode: SetupMode;
  let basePath = '/canvas';

  if (isApiOnly) {
    mode = 'api-only';
    console.log(pc.cyan('Mode: ') + 'API only (Node.js)');
  } else if (isNewProject) {
    mode = 'full';
    console.log(pc.cyan('Mode: ') + 'New Next.js project');
  } else if (projectInfo.type === 'nextjs') {
    // Existing Next.js project
    console.log(pc.cyan('Detected: ') + 'Existing Next.js project');

    if (projectInfo.hasConflictingRoutes) {
      console.log(pc.yellow('Warning: ') + 'Found existing catch-all route at root');
    }

    const response = await prompts([
      {
        type: 'select',
        name: 'mode',
        message: 'How would you like to add Canvas?',
        choices: [
          {
            title: 'Add at /canvas path (recommended for existing apps)',
            value: 'add-routes',
            description: 'Creates routes at /canvas/* without touching existing pages',
          },
          {
            title: 'Full setup at root /',
            value: 'full',
            description: 'Creates [[...path]] at root (may conflict with existing routes)',
          },
          {
            title: 'API only',
            value: 'api-only',
            description: 'Only add API routes, no UI components',
          },
        ],
        initial: projectInfo.hasConflictingRoutes ? 0 : 0,
      },
      {
        type: (prev) => prev === 'add-routes' ? 'text' : null,
        name: 'basePath',
        message: 'Base path for Canvas:',
        initial: '/canvas',
      },
    ]);

    if (!response.mode) {
      console.log(pc.red('Cancelled.'));
      process.exit(1);
    }

    mode = response.mode;
    basePath = response.basePath || '/canvas';
  } else if (projectInfo.type === 'nodejs') {
    // Existing Node.js project
    console.log(pc.cyan('Detected: ') + 'Existing Node.js project');
    mode = 'api-only';
  } else if (projectInfo.hasPackageJson) {
    // Unknown project type with package.json
    console.log(pc.cyan('Detected: ') + 'Existing project');

    const response = await prompts({
      type: 'select',
      name: 'mode',
      message: 'What would you like to create?',
      choices: [
        { title: 'Full Next.js app', value: 'full' },
        { title: 'API server only (Node.js)', value: 'api-only' },
      ],
    });

    if (!response.mode) {
      console.log(pc.red('Cancelled.'));
      process.exit(1);
    }

    mode = response.mode;
  } else {
    // Empty directory - ask what to create
    const response = await prompts({
      type: 'select',
      name: 'mode',
      message: 'What would you like to create?',
      choices: [
        { title: 'Full Next.js app (recommended)', value: 'full' },
        { title: 'API server only (Node.js)', value: 'api-only' },
      ],
    });

    if (!response.mode) {
      console.log(pc.red('Cancelled.'));
      process.exit(1);
    }

    mode = response.mode;
  }

  // Ask about template type for non-API modes
  let template: TemplateType = 'full';
  let configOptions: CanvasConfigOptions | undefined;

  if (mode !== 'api-only') {
    const templateResponse = await prompts({
      type: 'select',
      name: 'template',
      message: 'Setup type:',
      choices: [
        {
          title: '⚡ Quick setup (recommended)',
          value: 'minimal',
          description: 'Uses sensible defaults, customize later via canvas.config.ts',
        },
        {
          title: '🎨 Custom setup',
          value: 'custom',
          description: 'Configure brand, theme, and features now',
        },
      ],
      initial: 0,
    });

    if (!templateResponse.template) {
      console.log(pc.red('Cancelled.'));
      process.exit(1);
    }

    template = templateResponse.template;

    // Run configuration wizard for custom setup
    if (template === 'custom') {
      configOptions = await runConfigWizard();
    } else {
      // Use defaults for quick setup
      configOptions = getDefaultConfig();
    }
  }

  console.log();
  console.log(pc.cyan('Creating files...'));
  console.log();

  // Create project directory if new
  if (isNewProject) {
    fs.mkdirSync(projectDir, { recursive: true });
  }

  // Run setup based on mode
  const options: SetupOptions = {
    mode,
    template,
    projectDir,
    projectInfo,
    basePath,
    configOptions,
  };

  switch (mode) {
    case 'full':
      await setupFullNextJs(options);
      break;
    case 'add-routes':
      await setupAddRoutes(options);
      break;
    case 'api-only':
      await setupApiOnly(options);
      break;
  }

  // Success message
  console.log();
  console.log(pc.green('✨ Done!'));
  console.log();

  // Show what was configured
  if (configOptions && mode !== 'api-only') {
    console.log(pc.bold('Your Canvas app:'));
    console.log(pc.dim('  Name:     ') + pc.white(configOptions.brandName));
    console.log(pc.dim('  Theme:    ') + pc.white(configOptions.theme));
    console.log(pc.dim('  Provider: ') + pc.white(configOptions.llmProvider.toUpperCase()));
    console.log(pc.dim('  Features: ') + pc.white([
      configOptions.features.search && 'Search',
      configOptions.features.chat && 'Chat',
      configOptions.features.dashboard && 'Dashboard',
    ].filter(Boolean).join(', ')));
    console.log();
    console.log(pc.dim('  Edit canvas.config.ts to customize further.'));
    console.log();
  }

  console.log(pc.bold('Next steps:'));
  console.log();

  const pm = projectInfo.packageManager;

  if (isNewProject && targetArg && targetArg !== '.') {
    console.log(pc.cyan(`  1. cd ${targetArg}`));
    console.log(pc.cyan(`  2. ${pm} install`));
  } else {
    console.log(pc.cyan(`  1. ${pm} install`));
  }

  if (mode !== 'api-only') {
    const stepNum = isNewProject && targetArg && targetArg !== '.' ? 3 : 2;
    console.log(pc.cyan(`  ${stepNum}. cp .env.example .env.local`));
    console.log(pc.dim(`     # Add your ${configOptions?.llmProvider?.toUpperCase() || 'LLM'} API key`));
    console.log(pc.cyan(`  ${stepNum + 1}. ${pm} dev`));
    console.log();

    if (mode === 'add-routes') {
      console.log(pc.green(`🚀 Open http://localhost:3000${basePath}`));
    } else {
      console.log(pc.green('🚀 Open http://localhost:3000'));
    }
  } else {
    const stepNum = isNewProject && targetArg && targetArg !== '.' ? 3 : 2;
    console.log(pc.cyan(`  ${stepNum}. cp .env.example .env`));
    console.log(pc.dim('     # Add your LLM API key'));
    console.log(pc.cyan(`  ${stepNum + 1}. ${pm} start`));
    console.log();
    console.log(pc.green('🚀 API at http://localhost:3001/api/canvas/*'));
  }

  console.log();
  console.log(pc.dim('Need help? https://github.com/memvid-org/canvas'));
  console.log();
}

main().catch((err) => {
  console.error(pc.red('Error:'), err.message);
  process.exit(1);
});
