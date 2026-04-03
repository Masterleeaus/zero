/**
 * File Ingestion Module
 *
 * Handles text extraction from various file types and chunking for memory ingestion.
 * Supports PDF, DOCX, XLSX, PPTX, and other Office formats.
 */

// Chunking constants
const DEFAULT_CHUNK_CHARS = 1200;
const CHUNK_MIN_CHARS = DEFAULT_CHUNK_CHARS * 2; // 2400 chars minimum to trigger chunking

/**
 * Supported file extensions
 */
export const SUPPORTED_EXTENSIONS = [
  // Text files
  '.txt', '.md', '.json', '.csv',
  // Code files
  '.js', '.ts', '.jsx', '.tsx', '.py', '.rb', '.go', '.rs', '.java',
  '.c', '.cpp', '.h', '.hpp', '.cs', '.php', '.swift', '.kt', '.scala',
  '.html', '.css', '.scss', '.sass', '.less', '.sql', '.sh', '.bash',
  '.zsh', '.yaml', '.yml', '.xml', '.toml', '.ini', '.env',
  // Office documents
  '.pdf', '.docx', '.doc', '.xlsx', '.xls', '.pptx', '.ppt',
] as const;

/**
 * MIME types for binary files that need extraction
 */
export const BINARY_MIME_TYPES = [
  'application/pdf',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
  'application/msword', // doc
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
  'application/vnd.ms-excel', // xls
  'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
  'application/vnd.ms-powerpoint', // ppt
] as const;

/**
 * Check if a file extension is supported
 */
export function isExtensionSupported(filename: string): boolean {
  const ext = '.' + filename.toLowerCase().split('.').pop();
  return SUPPORTED_EXTENSIONS.includes(ext as typeof SUPPORTED_EXTENSIONS[number]);
}

/**
 * Check if a file is a binary format that needs text extraction
 */
export function isBinaryFormat(filename: string): boolean {
  const ext = filename.toLowerCase().split('.').pop();
  return ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt'].includes(ext || '');
}

/**
 * Extract text from a PDF buffer
 */
export async function extractPdfText(buffer: Buffer): Promise<string> {
  const { extractText } = await import('unpdf');
  const { text } = await extractText(new Uint8Array(buffer), { mergePages: true });
  return text;
}

/**
 * Extract text from a DOCX buffer
 */
export async function extractDocxText(buffer: Buffer): Promise<string> {
  const mammoth = await import('mammoth');
  const result = await mammoth.extractRawText({ buffer });
  return result.value;
}

/**
 * Extract text from an XLSX/XLS buffer
 */
export async function extractXlsxText(buffer: Buffer): Promise<string> {
  const XLSX = await import('xlsx');
  const workbook = XLSX.read(buffer, { type: 'buffer' });
  const lines: string[] = [];

  for (const sheetName of workbook.SheetNames) {
    lines.push(`--- Sheet: ${sheetName} ---`);
    const sheet = workbook.Sheets[sheetName];
    if (!sheet) continue;
    // Convert sheet to array of arrays
    const data = XLSX.utils.sheet_to_json(sheet, { header: 1 }) as unknown[][];

    for (const row of data) {
      const values = row.filter(v => v != null && v !== '');
      if (values.length > 0) {
        lines.push(values.join(' | '));
      }
    }
  }

  return lines.join('\n');
}

/**
 * Extract text from PPTX, DOC, and other Office files using officeparser
 */
export async function extractOfficeText(buffer: Buffer): Promise<string> {
  const { parseOffice } = await import('officeparser');
  const ast = await parseOffice(buffer);
  return ast.toText();
}

/**
 * Extract text from any supported binary file based on extension
 */
export async function extractText(buffer: Buffer, filename: string): Promise<string> {
  const ext = filename.toLowerCase().split('.').pop();

  switch (ext) {
    case 'pdf':
      return extractPdfText(buffer);
    case 'docx':
      return extractDocxText(buffer);
    case 'xlsx':
    case 'xls':
      return extractXlsxText(buffer);
    case 'pptx':
    case 'ppt':
    case 'doc':
      return extractOfficeText(buffer);
    default:
      // Try officeparser as fallback for unknown binary formats
      try {
        return await extractOfficeText(buffer);
      } catch {
        // If extraction fails, try to read as text
        return buffer.toString('utf-8');
      }
  }
}

/**
 * Chunk text into segments of approximately maxChars characters.
 * Uses smart breaking at sentence/paragraph boundaries.
 */
export function chunkText(text: string, maxChars: number = DEFAULT_CHUNK_CHARS): string[] {
  // Normalize whitespace
  const normalized = text.replace(/\s+/g, ' ').trim();

  // If text is too short, don't chunk
  if (normalized.length < CHUNK_MIN_CHARS) {
    return [normalized];
  }

  const chunks: string[] = [];
  let remaining = normalized;

  while (remaining.length > 0) {
    if (remaining.length <= maxChars) {
      chunks.push(remaining.trim());
      break;
    }

    // Find the best break point within maxChars
    let breakPoint = maxChars;

    // Try to break at paragraph (double newline)
    const paragraphBreak = remaining.lastIndexOf('\n\n', maxChars);
    if (paragraphBreak > maxChars * 0.5) {
      breakPoint = paragraphBreak;
    } else {
      // Try to break at sentence end (.!?)
      const sentenceMatch = remaining.slice(0, maxChars).match(/[.!?]\s+(?=[A-Z])/g);
      if (sentenceMatch && sentenceMatch.length > 0) {
        const lastMatch = sentenceMatch[sentenceMatch.length - 1]!;
        const lastSentence = remaining.slice(0, maxChars).lastIndexOf(lastMatch);
        if (lastSentence > maxChars * 0.5) {
          breakPoint = lastSentence + lastMatch.length;
        }
      } else {
        // Fall back to whitespace
        const spaceBreak = remaining.lastIndexOf(' ', maxChars);
        if (spaceBreak > maxChars * 0.5) {
          breakPoint = spaceBreak;
        }
      }
    }

    chunks.push(remaining.slice(0, breakPoint).trim());
    remaining = remaining.slice(breakPoint).trim();
  }

  return chunks.filter(chunk => chunk.length > 0);
}

/**
 * File input for ingestion
 */
export interface FileInput {
  /** File name with extension */
  filename: string;
  /** File content as Buffer */
  buffer: Buffer;
  /** Optional label/category for the file */
  label?: string;
  /** Optional metadata to attach */
  metadata?: Record<string, unknown>;
}

/**
 * Options for file ingestion
 */
export interface IngestOptions {
  /** Characters per chunk (default: 1200) */
  chunkSize?: number;
  /** Enable embeddings for semantic search */
  enableEmbeddings?: boolean;
  /** Embedding model to use */
  embeddingModel?: string;
  /** Progress callback */
  onProgress?: (current: number, total: number, filename: string) => void;
}

/**
 * Result of file ingestion
 */
export interface IngestResult {
  /** Number of frames created */
  frameCount: number;
  /** Frame IDs created */
  frameIds: string[];
  /** Files processed */
  filesProcessed: number;
  /** Any errors that occurred */
  errors: Array<{ filename: string; error: string }>;
}

/**
 * Ingest a single file into memory
 */
export async function ingestFile(
  memoryClient: { put: (doc: Record<string, unknown>) => Promise<string>; putMany: (docs: Record<string, unknown>[], options?: Record<string, unknown>) => Promise<string[]> },
  file: FileInput,
  options: IngestOptions = {}
): Promise<IngestResult> {
  const {
    chunkSize = DEFAULT_CHUNK_CHARS,
    enableEmbeddings = true,
    onProgress,
  } = options;

  const frameIds: string[] = [];
  const errors: Array<{ filename: string; error: string }> = [];

  try {
    // Extract text from file
    let text: string;
    if (isBinaryFormat(file.filename)) {
      text = await extractText(file.buffer, file.filename);
    } else {
      text = file.buffer.toString('utf-8');
    }

    if (!text || text.trim().length === 0) {
      // Empty file, create placeholder
      const frameId = await memoryClient.put({
        title: file.filename,
        text: '(Empty document)',
        label: file.label || 'document',
        metadata: file.metadata,
      });
      frameIds.push(frameId);
      return { frameCount: 1, frameIds, filesProcessed: 1, errors };
    }

    // Chunk the text
    const chunks = chunkText(text, chunkSize);

    if (onProgress) {
      onProgress(0, chunks.length, file.filename);
    }

    if (chunks.length === 1) {
      // Single chunk, put directly
      const frameId = await memoryClient.put({
        title: file.filename,
        text: chunks[0],
        label: file.label || 'document',
        metadata: { ...file.metadata, totalChunks: 1 },
        enableEmbedding: enableEmbeddings,
      });
      frameIds.push(frameId);
    } else {
      // Multiple chunks - create parent + chunk frames
      // Parent frame (stores beginning of text for preview)
      const parentFrameId = await memoryClient.put({
        title: file.filename,
        text: text.slice(0, 5000),
        label: file.label || 'document',
        metadata: { ...file.metadata, isParent: true, totalChunks: chunks.length },
      });
      frameIds.push(parentFrameId);

      // Chunk frames with embeddings
      const chunkDocs = chunks.map((chunk, index) => ({
        title: `${file.filename} (chunk ${index + 1}/${chunks.length})`,
        text: chunk,
        labels: [`${file.label || 'document'}-chunk`],
        metadata: {
          ...file.metadata,
          parentTitle: file.filename,
          chunk: index + 1,
          totalChunks: chunks.length,
        },
        enableEmbedding: enableEmbeddings,
      }));

      const chunkFrameIds = await memoryClient.putMany(chunkDocs);
      frameIds.push(...chunkFrameIds);
    }

    if (onProgress) {
      onProgress(chunks.length, chunks.length, file.filename);
    }

    return { frameCount: frameIds.length, frameIds, filesProcessed: 1, errors };
  } catch (error) {
    errors.push({
      filename: file.filename,
      error: error instanceof Error ? error.message : 'Unknown error',
    });
    return { frameCount: 0, frameIds: [], filesProcessed: 0, errors };
  }
}

/**
 * Ingest multiple files into memory
 */
export async function ingestFiles(
  memoryClient: { put: (doc: Record<string, unknown>) => Promise<string>; putMany: (docs: Record<string, unknown>[], options?: Record<string, unknown>) => Promise<string[]> },
  files: FileInput[],
  options: IngestOptions = {}
): Promise<IngestResult> {
  const { onProgress } = options;

  let totalFrameCount = 0;
  const allFrameIds: string[] = [];
  let filesProcessed = 0;
  const allErrors: Array<{ filename: string; error: string }> = [];

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (!file) continue;

    // Wrap progress to show overall progress
    const fileProgress = onProgress
      ? (_current: number, _total: number, filename: string) => {
          onProgress(i, files.length, filename);
        }
      : undefined;

    const result = await ingestFile(memoryClient, file, { ...options, onProgress: fileProgress });

    totalFrameCount += result.frameCount;
    allFrameIds.push(...result.frameIds);
    filesProcessed += result.filesProcessed;
    allErrors.push(...result.errors);
  }

  if (onProgress) {
    onProgress(files.length, files.length, 'Complete');
  }

  return {
    frameCount: totalFrameCount,
    frameIds: allFrameIds,
    filesProcessed,
    errors: allErrors,
  };
}

/**
 * Parse text content from a file buffer based on type
 */
export async function parseFileContent(
  buffer: Buffer,
  filename: string,
  mimeType?: string
): Promise<string> {
  // Check if it's a binary format
  if (isBinaryFormat(filename)) {
    return extractText(buffer, filename);
  }

  // Text-based files
  const ext = filename.toLowerCase().split('.').pop();

  // JSON files - pretty print
  if (ext === 'json' || mimeType === 'application/json') {
    try {
      const json = JSON.parse(buffer.toString('utf-8'));
      return JSON.stringify(json, null, 2);
    } catch {
      return buffer.toString('utf-8');
    }
  }

  // All other text files
  return buffer.toString('utf-8');
}

/**
 * Get file info from filename
 */
export function getFileInfo(filename: string): {
  extension: string;
  isBinary: boolean;
  isSupported: boolean;
  mimeType: string;
} {
  const ext = filename.toLowerCase().split('.').pop() || '';
  const isBinary = isBinaryFormat(filename);
  const isSupported = isExtensionSupported(filename);

  // Map extension to MIME type
  const mimeTypes: Record<string, string> = {
    pdf: 'application/pdf',
    docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    doc: 'application/msword',
    xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    xls: 'application/vnd.ms-excel',
    pptx: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ppt: 'application/vnd.ms-powerpoint',
    txt: 'text/plain',
    md: 'text/markdown',
    json: 'application/json',
    csv: 'text/csv',
    html: 'text/html',
    css: 'text/css',
    js: 'application/javascript',
    ts: 'application/typescript',
    xml: 'application/xml',
    yaml: 'application/x-yaml',
    yml: 'application/x-yaml',
  };

  return {
    extension: ext,
    isBinary,
    isSupported,
    mimeType: mimeTypes[ext] || 'application/octet-stream',
  };
}
