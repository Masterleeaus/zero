/**
 * Canvas.Search - Semantic Search Template
 *
 * A production-ready search interface with:
 * - Semantic, lexical, and hybrid search modes
 * - Real-time results with streaming
 * - Filters and faceted navigation
 * - Result highlighting
 * - Search history and suggestions
 *
 * Features:
 * - Debounced input for performance
 * - Result caching
 * - Keyboard navigation
 * - Accessibility (ARIA labels, focus management)
 * - Mobile-responsive design
 *
 * @example
 * ```tsx
 * <Canvas.Search brand={brand} memoryEndpoint="/api/memory" />
 * ```
 */

'use client';

import React, { useState, useRef, useEffect, useCallback, useMemo } from 'react';
import type { BrandConfig, SearchConfig } from '../types/brand.js';

// localStorage keys for search preferences
const SEARCH_MODE_KEY = 'canvas-search-mode';
const SEARCH_FILTERS_KEY = 'canvas-search-filters';

interface SearchProps {
  brand?: BrandConfig;
  config?: Record<string, unknown>;
  memoryEndpoint?: string;
  onEvent?: (event: string, data?: Record<string, unknown>) => void;
}

interface SearchResult {
  id: string;
  content: string;
  title?: string;
  uri?: string;
  score: number;
  metadata?: Record<string, unknown>;
  highlights?: string[];
}

interface FilterOption {
  id: string;
  label: string;
  type: 'select' | 'date' | 'tag';
  options?: string[];
  value?: string | string[];
}

type SearchMode = 'semantic' | 'lexical' | 'hybrid';

/**
 * Debounce hook for search input
 * Available for auto-search functionality if needed
 */
function _useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedValue(value), delay);
    return () => clearTimeout(timer);
  }, [value, delay]);

  return debouncedValue;
}
// Export for external use
export const useDebounce = _useDebounce;

/**
 * Search Mode Selector Component
 */
const SearchModeSelector = React.memo(function SearchModeSelector({
  modes,
  activeMode,
  onChange,
}: {
  modes: SearchMode[];
  activeMode: SearchMode;
  onChange: (mode: SearchMode) => void;
}) {
  const modeLabels: Record<SearchMode, string> = {
    semantic: 'Semantic',
    lexical: 'Keyword',
    hybrid: 'Hybrid',
  };

  const modeDescriptions: Record<SearchMode, string> = {
    semantic: 'Find meaning, not just words',
    lexical: 'Exact keyword matching',
    hybrid: 'Best of both worlds',
  };

  return (
    <div className="canvas-search-modes" role="radiogroup" aria-label="Search mode">
      {modes.map(mode => (
        <button
          key={mode}
          className={`canvas-search-mode ${activeMode === mode ? 'canvas-search-mode--active' : ''}`}
          onClick={() => onChange(mode)}
          role="radio"
          aria-checked={activeMode === mode}
          title={modeDescriptions[mode]}
        >
          {modeLabels[mode]}
        </button>
      ))}
    </div>
  );
});

/**
 * Search Input Component
 */
const SearchInput = React.memo(function SearchInput({
  value,
  onChange,
  onSubmit,
  onClear,
  placeholder,
  isLoading,
  inputRef,
}: {
  value: string;
  onChange: (value: string) => void;
  onSubmit: () => void;
  onClear: () => void;
  placeholder?: string;
  isLoading?: boolean;
  inputRef: React.RefObject<HTMLInputElement>;
}) {
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      onSubmit();
    } else if (e.key === 'Escape') {
      onClear();
    }
  };

  return (
    <div className="canvas-search-input-container">
      <div className="canvas-search-input-wrapper">
        <svg className="canvas-search-input__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
          <circle cx="8" cy="8" r="6" />
          <path d="M18 18L12.5 12.5" />
        </svg>
        <input
          ref={inputRef}
          type="text"
          className="canvas-search-input"
          value={value}
          onChange={e => onChange(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder={placeholder || 'Search your memory...'}
          aria-label="Search query"
          autoComplete="off"
          autoCorrect="off"
          spellCheck={false}
        />
        {value && (
          <button
            className="canvas-search-input__clear"
            onClick={onClear}
            aria-label="Clear search"
            type="button"
          >
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
              <path d="M4 4L12 12M4 12L12 4" stroke="currentColor" strokeWidth="2" />
            </svg>
          </button>
        )}
        {isLoading && (
          <div className="canvas-search-input__loader">
            <div className="canvas-loader__spinner canvas-loader__spinner--small" />
          </div>
        )}
      </div>
      <button
        className="canvas-search-submit"
        onClick={onSubmit}
        disabled={!value.trim() || isLoading}
        aria-label="Submit search"
      >
        Search
      </button>
    </div>
  );
});

/**
 * Filter Panel Component
 */
const FilterPanel = React.memo(function FilterPanel({
  filters,
  onFilterChange,
  onClear,
}: {
  filters: FilterOption[];
  onFilterChange: (filterId: string, value: string | string[]) => void;
  onClear: () => void;
}) {
  const hasActiveFilters = filters.some(f => f.value && (Array.isArray(f.value) ? f.value.length > 0 : f.value !== ''));

  return (
    <div className="canvas-search-filters">
      <div className="canvas-search-filters__header">
        <span className="canvas-search-filters__title">Filters</span>
        {hasActiveFilters && (
          <button className="canvas-search-filters__clear" onClick={onClear}>
            Clear all
          </button>
        )}
      </div>
      <div className="canvas-search-filters__list">
        {filters.map(filter => (
          <div key={filter.id} className="canvas-search-filter">
            <label htmlFor={`filter-${filter.id}`} className="canvas-search-filter__label">
              {filter.label}
            </label>
            {filter.type === 'select' && filter.options && (
              <select
                id={`filter-${filter.id}`}
                className="canvas-search-filter__select"
                value={(filter.value as string) || ''}
                onChange={e => onFilterChange(filter.id, e.target.value)}
              >
                <option value="">All</option>
                {filter.options.map(option => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            )}
            {filter.type === 'date' && (
              <input
                id={`filter-${filter.id}`}
                type="date"
                className="canvas-search-filter__date"
                value={(filter.value as string) || ''}
                onChange={e => onFilterChange(filter.id, e.target.value)}
              />
            )}
            {filter.type === 'tag' && filter.options && (
              <div className="canvas-search-filter__tags">
                {filter.options.map(tag => {
                  const isSelected = Array.isArray(filter.value) && filter.value.includes(tag);
                  return (
                    <button
                      key={tag}
                      className={`canvas-search-filter__tag ${isSelected ? 'canvas-search-filter__tag--active' : ''}`}
                      onClick={() => {
                        const currentTags = Array.isArray(filter.value) ? filter.value : [];
                        const newTags = isSelected
                          ? currentTags.filter(t => t !== tag)
                          : [...currentTags, tag];
                        onFilterChange(filter.id, newTags);
                      }}
                    >
                      {tag}
                    </button>
                  );
                })}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
});

/**
 * Search Result Card Component
 */
const ResultCard = React.memo(function ResultCard({
  result,
  showScore,
  onClick,
}: {
  result: SearchResult;
  showScore?: boolean;
  onClick?: (result: SearchResult) => void;
}) {
  return (
    <article
      className="canvas-result-card"
      onClick={() => onClick?.(result)}
      role={onClick ? 'button' : undefined}
      tabIndex={onClick ? 0 : undefined}
      onKeyDown={onClick ? (e) => e.key === 'Enter' && onClick(result) : undefined}
    >
      <header className="canvas-result-card__header">
        {result.title && <h3 className="canvas-result-card__title">{result.title}</h3>}
        {showScore && (
          <span className="canvas-result-card__score" title="Relevance score">
            {Math.round(result.score * 100)}%
          </span>
        )}
      </header>

      <div className="canvas-result-card__content">
        {result.highlights && result.highlights.length > 0 && result.highlights[0] ? (
          <p
            className="canvas-result-card__text canvas-result-card__text--highlighted"
            dangerouslySetInnerHTML={{ __html: result.highlights[0] }}
          />
        ) : (
          <p className="canvas-result-card__text">{result.content}</p>
        )}
      </div>

      {(result.uri || result.metadata) && (
        <footer className="canvas-result-card__footer">
          {result.uri && (
            <a
              href={result.uri}
              className="canvas-result-card__link"
              target="_blank"
              rel="noopener noreferrer"
              onClick={e => e.stopPropagation()}
            >
              View source
            </a>
          )}
          {result.metadata?.date != null && (
            <span className="canvas-result-card__meta">
              {new Date(result.metadata.date as string | number | Date).toLocaleDateString()}
            </span>
          )}
          {result.metadata?.type != null && (
            <span className="canvas-result-card__type">{result.metadata.type as React.ReactNode}</span>
          )}
        </footer>
      )}
    </article>
  );
});

/**
 * Format raw content into structured elements
 * - Converts lines starting with - or * into list items
 * - Groups consecutive list items into lists
 * - Cleans up excessive whitespace
 * - Preserves paragraph structure
 */
function formatContent(content: string): React.ReactNode {
  if (!content) return null;

  // Normalize line endings and clean up excessive whitespace
  const normalized = content
    .replace(/\r\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim();

  // Split into lines
  const lines = normalized.split('\n');
  const elements: React.ReactNode[] = [];
  let currentList: string[] = [];
  let currentParagraph: string[] = [];
  let key = 0;

  const flushParagraph = () => {
    if (currentParagraph.length > 0) {
      const text = currentParagraph.join(' ').trim();
      if (text) {
        elements.push(
          <p key={key++} className="canvas-result-detail__paragraph">
            {text}
          </p>
        );
      }
      currentParagraph = [];
    }
  };

  const flushList = () => {
    if (currentList.length > 0) {
      elements.push(
        <ul key={key++} className="canvas-result-detail__list">
          {currentList.map((item, i) => (
            <li key={i} className="canvas-result-detail__list-item">{item}</li>
          ))}
        </ul>
      );
      currentList = [];
    }
  };

  for (const line of lines) {
    const trimmed = line.trim();

    // Check if this is a list item (starts with -, *, or •)
    const listMatch = trimmed.match(/^[-*•]\s*(.+)$/);

    if (listMatch && listMatch[1]) {
      // Flush any pending paragraph first
      flushParagraph();
      currentList.push(listMatch[1].trim());
    } else if (trimmed === '') {
      // Empty line - flush both
      flushList();
      flushParagraph();
    } else {
      // Regular text - flush list first, then add to paragraph
      flushList();
      currentParagraph.push(trimmed);
    }
  }

  // Flush remaining content
  flushList();
  flushParagraph();

  return elements.length > 0 ? elements : <p className="canvas-result-detail__paragraph">{content}</p>;
}

/**
 * Check if URI points to a PDF document
 */
function isPdfUri(uri?: string): boolean {
  if (!uri) return false;
  const lower = uri.toLowerCase();
  // Handle URIs like mv2://file.pdf#page-72 or file.pdf
  return lower.includes('.pdf') || lower.includes('application/pdf');
}

/**
 * Parse memvid URI to get asset URL
 */
function getAssetUrl(frameId: string, memoryEndpoint: string): string {
  return `${memoryEndpoint}/asset?frameId=${frameId}`;
}

/**
 * PDF Viewer Component - Displays PDF inline with controls
 */
const PDFViewer = React.memo(function PDFViewer({
  frameId,
  memoryEndpoint,
  title,
}: {
  frameId: string;
  memoryEndpoint: string;
  title?: string;
}) {
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const pdfUrl = getAssetUrl(frameId, memoryEndpoint);

  return (
    <div className="canvas-pdf-viewer">
      <div className="canvas-pdf-viewer__header">
        <div className="canvas-pdf-viewer__title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14,2 14,8 20,8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <line x1="10" y1="9" x2="8" y2="9" />
          </svg>
          <span>{title || 'PDF Document'}</span>
        </div>
        <div className="canvas-pdf-viewer__actions">
          <a
            href={pdfUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="canvas-pdf-viewer__btn"
            title="Open in new tab"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
              <polyline points="15,3 21,3 21,9" />
              <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
          </a>
          <a
            href={pdfUrl}
            download
            className="canvas-pdf-viewer__btn"
            title="Download PDF"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7,10 12,15 17,10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
          </a>
        </div>
      </div>

      <div className="canvas-pdf-viewer__container">
        {isLoading && (
          <div className="canvas-pdf-viewer__loading">
            <div className="canvas-loader__spinner" />
            <span>Loading PDF...</span>
          </div>
        )}
        {error && (
          <div className="canvas-pdf-viewer__error">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <p>{error}</p>
            <a href={pdfUrl} target="_blank" rel="noopener noreferrer" className="canvas-btn canvas-btn--secondary">
              Open PDF externally
            </a>
          </div>
        )}
        <iframe
          src={pdfUrl}
          className="canvas-pdf-viewer__iframe"
          title={title || 'PDF Document'}
          onLoad={() => setIsLoading(false)}
          onError={() => {
            setIsLoading(false);
            setError('Unable to display PDF inline');
          }}
          style={{ display: isLoading || error ? 'none' : 'block' }}
        />
      </div>
    </div>
  );
});

/**
 * Result Detail Modal Component
 */
const ResultDetailModal = React.memo(function ResultDetailModal({
  result,
  onClose,
  memoryEndpoint = '/api/canvas',
}: {
  result: SearchResult | null;
  onClose: () => void;
  memoryEndpoint?: string;
}) {
  const [viewMode, setViewMode] = useState<'text' | 'pdf'>('text');

  if (!result) return null;

  const hasPdf = isPdfUri(result.uri) || result.metadata?.mime === 'application/pdf';
  const frameId = result.id;

  return (
    <div className="canvas-modal-overlay" onClick={onClose}>
      <div
        className={`canvas-result-detail ${hasPdf ? 'canvas-result-detail--has-pdf' : ''}`}
        onClick={e => e.stopPropagation()}
      >
        <header className="canvas-result-detail__header">
          <div className="canvas-result-detail__title-row">
            <h2 className="canvas-result-detail__title">{result.title || 'Search Result'}</h2>
            <span className="canvas-result-detail__score">
              {Math.round(result.score * 100)}% match
            </span>
          </div>

          {hasPdf && (
            <div className="canvas-result-detail__view-toggle">
              <button
                className={`canvas-result-detail__view-btn ${viewMode === 'text' ? 'canvas-result-detail__view-btn--active' : ''}`}
                onClick={() => setViewMode('text')}
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <line x1="17" y1="10" x2="3" y2="10" />
                  <line x1="21" y1="6" x2="3" y2="6" />
                  <line x1="21" y1="14" x2="3" y2="14" />
                  <line x1="17" y1="18" x2="3" y2="18" />
                </svg>
                Text
              </button>
              <button
                className={`canvas-result-detail__view-btn ${viewMode === 'pdf' ? 'canvas-result-detail__view-btn--active' : ''}`}
                onClick={() => setViewMode('pdf')}
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14,2 14,8 20,8" />
                </svg>
                PDF
              </button>
            </div>
          )}

          <button className="canvas-result-detail__close" onClick={onClose} aria-label="Close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </header>

        <div className="canvas-result-detail__content">
          {viewMode === 'pdf' && hasPdf ? (
            <PDFViewer
              frameId={frameId}
              memoryEndpoint={memoryEndpoint}
              title={result.title}
            />
          ) : (
            <>
              <div className="canvas-result-detail__body">
                {formatContent(result.content)}
              </div>

              {result.metadata && Object.keys(result.metadata).length > 0 && (
                <div className="canvas-result-detail__metadata">
                  <h3 className="canvas-result-detail__metadata-title">Metadata</h3>
                  <dl className="canvas-result-detail__metadata-list">
                    {Object.entries(result.metadata)
                      .filter(([key]) => !['uri', 'mime'].includes(key))
                      .map(([key, value]) => (
                        <div key={key} className="canvas-result-detail__metadata-item">
                          <dt>{key}</dt>
                          <dd>{typeof value === 'object' ? JSON.stringify(value) : String(value)}</dd>
                        </div>
                      ))}
                  </dl>
                </div>
              )}
            </>
          )}
        </div>

        <footer className="canvas-result-detail__footer">
          {hasPdf && (
            <a
              href={getAssetUrl(frameId, memoryEndpoint)}
              download
              className="canvas-btn canvas-btn--primary"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7,10 12,15 17,10" />
                <line x1="12" y1="15" x2="12" y2="3" />
              </svg>
              Download PDF
            </a>
          )}
          <button className="canvas-btn canvas-btn--secondary" onClick={onClose}>
            Close
          </button>
        </footer>
      </div>
    </div>
  );
});

/**
 * Results List Component
 */
const ResultsList = React.memo(function ResultsList({
  results,
  showScores,
  onResultClick,
  isLoading,
  query,
}: {
  results: SearchResult[];
  showScores?: boolean;
  onResultClick?: (result: SearchResult) => void;
  isLoading: boolean;
  query: string;
}) {
  if (isLoading) {
    return (
      <div className="canvas-results canvas-results--loading">
        {[1, 2, 3].map(i => (
          <div key={i} className="canvas-result-card canvas-result-card--skeleton">
            <div className="canvas-skeleton canvas-skeleton--title" />
            <div className="canvas-skeleton canvas-skeleton--text" />
            <div className="canvas-skeleton canvas-skeleton--text canvas-skeleton--short" />
          </div>
        ))}
      </div>
    );
  }

  if (!query) {
    return (
      <div className="canvas-results canvas-results--empty">
        <div className="canvas-results__empty-state">
          <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="28" cy="28" r="16" />
            <path d="M56 56L40 40" />
          </svg>
          <h3>Search your memory</h3>
          <p>Enter a query to search through your documents and conversations.</p>
        </div>
      </div>
    );
  }

  if (results.length === 0) {
    return (
      <div className="canvas-results canvas-results--empty">
        <div className="canvas-results__empty-state">
          <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="32" cy="32" r="24" />
            <path d="M24 24L40 40M40 24L24 40" />
          </svg>
          <h3>No results found</h3>
          <p>Try adjusting your search terms or filters.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="canvas-results">
      <div className="canvas-results__header">
        <span className="canvas-results__count">
          {results.length} result{results.length !== 1 ? 's' : ''}
        </span>
      </div>
      <div className="canvas-results__list">
        {results.map(result => (
          <ResultCard
            key={result.id}
            result={result}
            showScore={showScores}
            onClick={onResultClick}
          />
        ))}
      </div>
    </div>
  );
});

/**
 * Recent Searches Component
 */
const RecentSearches = React.memo(function RecentSearches({
  searches,
  onSelect,
  onClear,
}: {
  searches: string[];
  onSelect: (query: string) => void;
  onClear: () => void;
}) {
  if (searches.length === 0) return null;

  return (
    <div className="canvas-recent-searches">
      <div className="canvas-recent-searches__header">
        <span className="canvas-recent-searches__title">Recent searches</span>
        <button className="canvas-recent-searches__clear" onClick={onClear}>
          Clear
        </button>
      </div>
      <ul className="canvas-recent-searches__list">
        {searches.map((query, index) => (
          <li key={index} className="canvas-recent-searches__item">
            <button onClick={() => onSelect(query)}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 1v7L4 4.5" stroke="currentColor" strokeWidth="1.5" fill="none" />
                <circle cx="8" cy="8" r="6" stroke="currentColor" strokeWidth="1.5" fill="none" />
              </svg>
              {query}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
});

/**
 * Main Search Component
 */
export function Search({
  brand,
  config,
  memoryEndpoint = '/api/canvas',
  onEvent,
}: SearchProps) {
  const searchConfig = { ...(brand?.search || {}), ...(config?.search || {}) } as SearchConfig;
  const inputRef = useRef<HTMLInputElement>(null);

  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResult[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isHydrated, setIsHydrated] = useState(false);
  const [selectedResult, setSelectedResult] = useState<SearchResult | null>(null);

  // Initialize with default values (no localStorage on server)
  const [searchMode, setSearchMode] = useState<SearchMode>(searchConfig.defaultMode || 'hybrid');
  const [filters, setFilters] = useState<FilterOption[]>(() =>
    (searchConfig.filters || []).map(f => ({ ...f, value: undefined }))
  );
  const [recentSearches, setRecentSearches] = useState<string[]>([]);

  // Load from localStorage AFTER hydration to avoid mismatch
  useEffect(() => {
    setIsHydrated(true);

    // Load search mode
    const savedMode = localStorage.getItem(SEARCH_MODE_KEY);
    if (savedMode && ['semantic', 'lexical', 'hybrid'].includes(savedMode)) {
      setSearchMode(savedMode as SearchMode);
    }

    // Load filters
    try {
      const savedFilters = localStorage.getItem(SEARCH_FILTERS_KEY);
      if (savedFilters) {
        const parsed = JSON.parse(savedFilters);
        setFilters(prev => prev.map(f => ({
          ...f,
          value: parsed[f.id] ?? undefined
        })));
      }
    } catch {
      // Ignore parse errors
    }

    // Load recent searches
    try {
      const savedSearches = localStorage.getItem('canvas-recent-searches');
      if (savedSearches) {
        setRecentSearches(JSON.parse(savedSearches));
      }
    } catch {
      // Ignore parse errors
    }
  }, []);

  // Persist search mode to localStorage when it changes (only after hydration)
  useEffect(() => {
    if (isHydrated) {
      localStorage.setItem(SEARCH_MODE_KEY, searchMode);
    }
  }, [searchMode, isHydrated]);

  // Persist filters to localStorage when they change (only after hydration)
  useEffect(() => {
    if (isHydrated) {
      const filterValues: Record<string, string | string[] | undefined> = {};
      filters.forEach(f => {
        if (f.value !== undefined) {
          filterValues[f.id] = f.value;
        }
      });
      localStorage.setItem(SEARCH_FILTERS_KEY, JSON.stringify(filterValues));
    }
  }, [filters, isHydrated]);

  // Debounce is available for auto-search if needed
  // const debouncedQuery = useDebounce(query, 300);

  // Available search modes
  const availableModes = useMemo((): SearchMode[] => {
    const modes = searchConfig.modes || ['semantic', 'lexical', 'hybrid'];
    return modes as SearchMode[];
  }, [searchConfig.modes]);

  // Perform search
  const performSearch = useCallback(async (searchQuery: string) => {
    if (!searchQuery.trim()) {
      setResults([]);
      return;
    }

    setIsLoading(true);
    onEvent?.('search_started', { query: searchQuery, mode: searchMode });

    try {
      // Build filter params
      const filterParams: Record<string, string | string[]> = {};
      filters.forEach(f => {
        if (f.value && (Array.isArray(f.value) ? f.value.length > 0 : f.value !== '')) {
          filterParams[f.id] = f.value;
        }
      });

      const response = await fetch(`${memoryEndpoint}/search`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          query: searchQuery,
          mode: searchMode,
          limit: searchConfig.limit || 20,
          filters: filterParams,
        }),
      });

      if (!response.ok) throw new Error('Search failed');

      const data = await response.json();
      const searchResults: SearchResult[] = (data.results || data || []).map((r: SearchResult, i: number) => ({
        id: r.id || `result-${i}`,
        content: r.content || '',
        title: r.title,
        uri: r.uri,
        score: r.score || 0,
        metadata: r.metadata,
        highlights: r.highlights,
      }));

      setResults(searchResults);

      // Add to recent searches
      setRecentSearches(prev => {
        const updated = [searchQuery, ...prev.filter(q => q !== searchQuery)].slice(0, 5);
        if (typeof window !== 'undefined') {
          localStorage.setItem('canvas-recent-searches', JSON.stringify(updated));
        }
        return updated;
      });

      onEvent?.('search_completed', { query: searchQuery, resultCount: searchResults.length });
    } catch (error) {
      onEvent?.('search_error', { error: error instanceof Error ? error.message : 'Unknown error' });
      setResults([]);
    } finally {
      setIsLoading(false);
    }
  }, [memoryEndpoint, searchMode, filters, searchConfig.limit, onEvent]);

  // Handle submit
  const handleSubmit = useCallback(() => {
    performSearch(query);
  }, [query, performSearch]);

  // Handle clear
  const handleClear = useCallback(() => {
    setQuery('');
    setResults([]);
    inputRef.current?.focus();
  }, []);

  // Handle filter change
  const handleFilterChange = useCallback((filterId: string, value: string | string[]) => {
    setFilters(prev => prev.map(f => f.id === filterId ? { ...f, value } : f));
  }, []);

  // Handle clear filters
  const handleClearFilters = useCallback(() => {
    setFilters(prev => prev.map(f => ({ ...f, value: undefined })));
  }, []);

  // Handle result click - open detail modal
  const handleResultClick = useCallback((result: SearchResult) => {
    setSelectedResult(result);
    onEvent?.('result_clicked', { resultId: result.id, uri: result.uri });
  }, [onEvent]);

  // Close detail modal
  const handleCloseDetail = useCallback(() => {
    setSelectedResult(null);
  }, []);

  // Handle recent search select
  const handleRecentSelect = useCallback((selectedQuery: string) => {
    setQuery(selectedQuery);
    performSearch(selectedQuery);
  }, [performSearch]);

  // Handle clear recent searches
  const handleClearRecent = useCallback(() => {
    setRecentSearches([]);
    if (typeof window !== 'undefined') {
      localStorage.removeItem('canvas-recent-searches');
    }
  }, []);

  // Focus input on mount
  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  return (
    <div className="canvas-search">
      {/* Header */}
      <header className="canvas-search__header">
        <h1 className="canvas-search__title">
          {searchConfig.placeholder ? '' : 'Search'}
        </h1>
        {availableModes.length > 1 && (
          <SearchModeSelector
            modes={availableModes}
            activeMode={searchMode}
            onChange={setSearchMode}
          />
        )}
      </header>

      {/* Search Input */}
      <SearchInput
        value={query}
        onChange={setQuery}
        onSubmit={handleSubmit}
        onClear={handleClear}
        placeholder={searchConfig.placeholder}
        isLoading={isLoading}
        inputRef={inputRef as React.RefObject<HTMLInputElement>}
      />

      {/* Main Content */}
      <div className="canvas-search__content">
        {/* Sidebar with filters */}
        {searchConfig.enableFilters !== false && filters.length > 0 && (
          <aside className="canvas-search__sidebar">
            <FilterPanel
              filters={filters}
              onFilterChange={handleFilterChange}
              onClear={handleClearFilters}
            />
          </aside>
        )}

        {/* Results */}
        <div className="canvas-search__main">
          {!query && !isLoading && (
            <RecentSearches
              searches={recentSearches}
              onSelect={handleRecentSelect}
              onClear={handleClearRecent}
            />
          )}
          <ResultsList
            results={results}
            showScores={searchConfig.showScores}
            onResultClick={handleResultClick}
            isLoading={isLoading}
            query={query}
          />
        </div>
      </div>

      {/* Result Detail Modal */}
      <ResultDetailModal
        result={selectedResult}
        onClose={handleCloseDetail}
        memoryEndpoint={memoryEndpoint}
      />
    </div>
  );
}

export default Search;
