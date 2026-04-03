/**
 * Token Estimation Utilities
 *
 * Rough token counting for various models.
 */

/**
 * Estimate tokens for a string
 * Uses a simple heuristic: ~4 characters per token for English text
 */
export function estimateTokens(text: string): number {
  if (!text) return 0;

  // Remove extra whitespace
  const normalized = text.trim().replace(/\s+/g, ' ');

  // Use character-based estimation
  // Claude/GPT models: ~4 chars per token for English
  return Math.ceil(normalized.length / 4);
}

/**
 * Estimate tokens for a message
 */
export function estimateMessageTokens(message: {
  role: string;
  content: string;
}): number {
  // Role overhead (~4 tokens)
  const roleTokens = 4;
  // Content tokens
  const contentTokens = estimateTokens(message.content);

  return roleTokens + contentTokens;
}

/**
 * Estimate tokens for an array of messages
 */
export function estimateMessagesTokens(
  messages: Array<{ role: string; content: string }>
): number {
  // Base overhead for message formatting (~3 tokens)
  const baseOverhead = 3;

  return (
    baseOverhead +
    messages.reduce((sum, msg) => sum + estimateMessageTokens(msg), 0)
  );
}

/**
 * Truncate text to fit within a token limit
 */
export function truncateToTokenLimit(text: string, maxTokens: number): string {
  const estimatedChars = maxTokens * 4;

  if (text.length <= estimatedChars) {
    return text;
  }

  // Truncate with ellipsis
  return text.slice(0, estimatedChars - 3) + '...';
}

/**
 * Split text into chunks that fit within a token limit
 */
export function chunkByTokens(text: string, maxTokensPerChunk: number): string[] {
  const chunks: string[] = [];
  const maxChars = maxTokensPerChunk * 4;

  // Split by paragraphs first
  const paragraphs = text.split(/\n\n+/);
  let currentChunk = '';

  for (const paragraph of paragraphs) {
    if (currentChunk.length + paragraph.length + 2 <= maxChars) {
      currentChunk += (currentChunk ? '\n\n' : '') + paragraph;
    } else {
      if (currentChunk) {
        chunks.push(currentChunk);
      }

      // Handle paragraphs longer than maxChars
      if (paragraph.length > maxChars) {
        // Split by sentences
        const sentences = paragraph.match(/[^.!?]+[.!?]+/g) || [paragraph];
        currentChunk = '';

        for (const sentence of sentences) {
          if (currentChunk.length + sentence.length <= maxChars) {
            currentChunk += sentence;
          } else {
            if (currentChunk) {
              chunks.push(currentChunk);
            }
            currentChunk = sentence.slice(0, maxChars);
          }
        }
      } else {
        currentChunk = paragraph;
      }
    }
  }

  if (currentChunk) {
    chunks.push(currentChunk);
  }

  return chunks;
}
