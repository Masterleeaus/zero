/**
 * useMemory Hook
 *
 * Hook for searching and managing memory context.
 */

import { useCallback, useState } from 'react';
import { useEngine } from '../context/index.js';
import type { RecallResult, FrameType } from '@memvid/canvas-core/types-only';

/**
 * useMemory options
 */
export interface UseMemoryOptions {
  /** Maximum results to return */
  maxResults?: number;

  /** Minimum similarity score (0-1) */
  minScore?: number;

  /** Filter by frame types */
  types?: string[];
}

/**
 * useMemory return type
 */
export interface UseMemoryReturn {
  /** Search results */
  results: RecallResult[];

  /** Whether a search is in progress */
  isSearching: boolean;

  /** Error from last search */
  error: string | null;

  /** Search memory */
  search: (query: string, options?: UseMemoryOptions) => Promise<RecallResult[]>;

  /** Clear results */
  clear: () => void;
}

/**
 * Hook for memory search functionality
 *
 * @example
 * ```tsx
 * function MemorySearch() {
 *   const { results, search, isSearching } = useMemory();
 *
 *   const handleSearch = async () => {
 *     await search("What did we discuss about the project?");
 *   };
 *
 *   return (
 *     <div>
 *       <button onClick={handleSearch} disabled={isSearching}>
 *         Search Memory
 *       </button>
 *       {results.map((result, i) => (
 *         <div key={i}>{result.content}</div>
 *       ))}
 *     </div>
 *   );
 * }
 * ```
 */
export function useMemory(options: UseMemoryOptions = {}): UseMemoryReturn {
  const { maxResults = 10, minScore = 0.5, types } = options;

  const engine = useEngine();

  const [results, setResults] = useState<RecallResult[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [error, setError] = useState<string | null>(null);

  /**
   * Search memory
   */
  const search = useCallback(
    async (query: string, searchOptions?: UseMemoryOptions): Promise<RecallResult[]> => {
      if (!query.trim()) {
        return [];
      }

      setIsSearching(true);
      setError(null);

      try {
        const searchResults = await engine.recall({
          query,
          limit: searchOptions?.maxResults ?? maxResults,
          minScore: searchOptions?.minScore ?? minScore,
          types: (searchOptions?.types ?? types) as FrameType[] | undefined,
        });

        setResults(searchResults);
        return searchResults;
      } catch (err) {
        const errorMessage =
          err instanceof Error ? err.message : 'Memory search failed';
        setError(errorMessage);
        return [];
      } finally {
        setIsSearching(false);
      }
    },
    [engine, maxResults, minScore, types]
  );

  /**
   * Clear results
   */
  const clear = useCallback(() => {
    setResults([]);
    setError(null);
  }, []);

  return {
    results,
    isSearching,
    error,
    search,
    clear,
  };
}
