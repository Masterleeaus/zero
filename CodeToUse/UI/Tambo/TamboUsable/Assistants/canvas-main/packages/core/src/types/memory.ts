/**
 * Memory Types
 *
 * Types for .mv2 memory file operations.
 */

/**
 * Memory configuration
 */
export interface MemoryConfig {
  /** Path to .mv2 file (local or cloud) */
  path: string;

  /** Memvid API key (for cloud sync) */
  memvidApiKey?: string;

  /** Auto-create file if not exists */
  autoCreate?: boolean;

  /** Enable auto-save */
  autoSave?: boolean;

  /** Auto-save interval in milliseconds */
  autoSaveInterval?: number;
}

/**
 * Frame type (unit of storage in .mv2)
 */
export type FrameType =
  | 'message'
  | 'correction'
  | 'feedback'
  | 'decision'
  | 'preference'
  | 'pattern'
  | 'document'
  | 'custom';

/**
 * A frame (unit of storage in .mv2 file)
 */
export interface Frame {
  /** Unique frame ID */
  id: string;

  /** Frame type */
  type: FrameType;

  /** Frame content */
  content: string;

  /** When the frame was created */
  timestamp: Date;

  /** Frame metadata */
  metadata: Record<string, unknown>;

  /** Embedding vector (if indexed) */
  embedding?: number[];
}

/**
 * Search mode for memory recall
 */
export type SearchMode = 'semantic' | 'lexical' | 'hybrid';

/**
 * Memory search/recall options
 */
export interface RecallOptions {
  /** Search query */
  query: string;

  /** Search mode */
  mode?: SearchMode;

  /** Maximum results */
  limit?: number;

  /** Minimum relevance score (0-1) */
  minScore?: number;

  /** Filter by frame types */
  types?: FrameType[];

  /** Filter by time range */
  since?: Date;
  until?: Date;

  /** Filter by metadata */
  metadata?: Record<string, unknown>;

  /** Include embeddings in results */
  includeEmbeddings?: boolean;
}

/**
 * Search result
 */
export interface RecallResult {
  /** Frame ID */
  id: string;

  /** Frame type */
  type: FrameType;

  /** Frame content */
  content: string;

  /** Relevance score (0-1) */
  score: number;

  /** Frame timestamp */
  timestamp: Date;

  /** Frame metadata */
  metadata: Record<string, unknown>;
}

/**
 * Options for capturing to memory
 */
export interface CaptureOptions {
  /** Frame type */
  type: FrameType;

  /** Content to store (text content) */
  content: string;

  /** Optional file path for document ingestion (PDF, etc.) */
  filePath?: string;

  /** Optional metadata */
  metadata?: Record<string, unknown>;

  /** Generate embedding immediately */
  embed?: boolean;
}

/**
 * Correction record
 */
export interface Correction {
  /** Original message ID that was wrong */
  originalMessageId: string;

  /** What the AI did wrong */
  whatAiDid: string;

  /** The correct approach */
  correctApproach: string;

  /** Optional category tags */
  tags?: string[];
}

/**
 * User preference
 */
export interface Preference {
  /** Preference ID */
  id: string;

  /** Category */
  category: 'style' | 'pattern' | 'rejection' | 'convention';

  /** Preference content */
  content: string;

  /** Confidence score (0-1) */
  confidence: number;

  /** Evidence (frame IDs that support this preference) */
  evidence: string[];
}

/**
 * Memory statistics
 */
export interface MemoryStats {
  /** Total number of frames */
  totalFrames: number;

  /** Frames by type */
  framesByType: Record<FrameType, number>;

  /** File size in bytes */
  sizeBytes: number;

  /** Earliest frame timestamp */
  firstFrame?: Date;

  /** Latest frame timestamp */
  lastFrame?: Date;

  /** Number of indexed frames (with embeddings) */
  indexedFrames: number;
}

/**
 * Context built from memory for LLM
 */
export interface MemoryContext {
  /** Relevant frames from memory */
  frames: RecallResult[];

  /** User preferences */
  preferences: Preference[];

  /** Recent corrections */
  corrections: Correction[];

  /** Total tokens used by context */
  tokenCount: number;
}

/**
 * Options for building context
 */
export interface BuildContextOptions {
  /** Query to search for relevant context */
  query: string;

  /** Maximum tokens for context */
  maxTokens?: number;

  /** Include user preferences */
  includePreferences?: boolean;

  /** Include recent corrections */
  includeCorrections?: boolean;

  /** Boost recent frames */
  recencyBoost?: number;
}

/**
 * Frame information from CLI view command
 */
export interface FrameInfo {
  /** Frame ID */
  id: number;

  /** Frame URI (e.g., mv2://filename.pdf) */
  uri?: string;

  /** Frame title */
  title?: string;

  /** MIME type of the content */
  mimeType?: string;

  /** Total number of pages/chunks */
  pageCount: number;

  /** Current page number */
  currentPage: number;

  /** Text content of current page */
  content?: string;

  /** Additional metadata */
  metadata: Record<string, unknown>;
}
