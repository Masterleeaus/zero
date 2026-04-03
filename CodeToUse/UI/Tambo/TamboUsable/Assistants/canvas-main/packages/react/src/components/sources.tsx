/**
 * Sources Component
 *
 * Displays memory sources from recall.
 */

import { useState, useCallback, type ReactNode } from 'react';
import clsx from 'clsx';
import type { RecallResult } from '@memvid/canvas-core/types-only';

/**
 * Sources props
 */
export interface SourcesProps {
  /** Sources to display */
  sources: RecallResult[];

  /** Custom class name */
  className?: string;

  /** Title for the sources section */
  title?: string;

  /** Whether to show expanded by default */
  defaultExpanded?: boolean;

  /** Maximum sources to show initially */
  maxVisible?: number;

  /** Whether to show relevance scores */
  showScores?: boolean;

  /** Custom source renderer */
  renderSource?: (source: RecallResult, index: number) => ReactNode;

  /** Click handler for a source */
  onSourceClick?: (source: RecallResult) => void;
}

/**
 * Format score as percentage
 */
function formatScore(score: number): string {
  return `${Math.round(score * 100)}%`;
}

/**
 * Truncate content for preview
 */
function truncate(text: string, maxLength: number = 150): string {
  if (text.length <= maxLength) return text;
  return text.slice(0, maxLength).trim() + '...';
}

/**
 * Default source item renderer
 */
function DefaultSource({
  source,
  onClick,
  showScore = true,
}: {
  source: RecallResult;
  onClick?: () => void;
  showScore?: boolean;
}) {
  return (
    <div
      className="canvas-source"
      onClick={onClick}
      role={onClick ? 'button' : undefined}
      tabIndex={onClick ? 0 : undefined}
      onKeyDown={
        onClick
          ? (e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onClick();
              }
            }
          : undefined
      }
    >
      <div className="canvas-source__header">
        {showScore && (
          <span className="canvas-source__score" title="Relevance score">
            {formatScore(source.score)}
          </span>
        )}
        {typeof source.metadata?.timestamp === 'string' && (
          <span className="canvas-source__time">
            {new Date(source.metadata.timestamp).toLocaleDateString()}
          </span>
        )}
      </div>
      <div className="canvas-source__content">{truncate(source.content)}</div>
    </div>
  );
}

/**
 * Sources component
 *
 * @example
 * ```tsx
 * const { sources } = useChat();
 *
 * <Sources sources={sources} title="Referenced memories" />
 * ```
 */
export function Sources({
  sources,
  className,
  title = 'Sources',
  defaultExpanded = false,
  maxVisible = 3,
  showScores = true,
  renderSource,
  onSourceClick,
}: SourcesProps) {
  const [isExpanded, setIsExpanded] = useState(defaultExpanded);

  /**
   * Toggle expansion
   */
  const toggleExpand = useCallback(() => {
    setIsExpanded((prev) => !prev);
  }, []);

  if (sources.length === 0) {
    return null;
  }

  const visibleSources = isExpanded ? sources : sources.slice(0, maxVisible);
  const hasMore = sources.length > maxVisible;

  return (
    <div className={clsx('canvas-sources', className)}>
      <div className="canvas-sources__header">
        <span className="canvas-sources__title">{title}</span>
        <span className="canvas-sources__count">{sources.length}</span>
      </div>

      <div className="canvas-sources__list">
        {visibleSources.map((source, index) =>
          renderSource ? (
            renderSource(source, index)
          ) : (
            <DefaultSource
              key={index}
              source={source}
              showScore={showScores}
              onClick={onSourceClick ? () => onSourceClick(source) : undefined}
            />
          )
        )}
      </div>

      {hasMore && (
        <button
          className="canvas-sources__toggle"
          onClick={toggleExpand}
          aria-expanded={isExpanded}
        >
          {isExpanded ? 'Show less' : `Show ${sources.length - maxVisible} more`}
        </button>
      )}
    </div>
  );
}
