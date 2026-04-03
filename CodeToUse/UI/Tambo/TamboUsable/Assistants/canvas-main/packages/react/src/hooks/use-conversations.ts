/**
 * useConversations Hook
 *
 * Hook for managing multiple conversations.
 */

import { useCallback, useMemo } from 'react';
import { useStore as useZustand } from 'zustand';
import { useStore } from '../context/index.js';
import { selectConversations, selectChat } from '../context/canvas-store.js';
import type { Conversation } from '@memvid/canvas-core/types-only';

/**
 * useConversations return type
 */
export interface UseConversationsReturn {
  /** All conversations */
  conversations: Conversation[];

  /** Current conversation ID */
  currentId: string | null;

  /** Create a new conversation */
  create: (id?: string) => string;

  /** Switch to a conversation */
  select: (id: string) => void;

  /** Delete a conversation */
  remove: (id: string) => void;

  /** Check if conversation exists */
  has: (id: string) => boolean;

  /** Get conversation by ID */
  get: (id: string) => Conversation | undefined;
}

/**
 * Generate a unique conversation ID
 */
function generateId(): string {
  return `conv_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
}

/**
 * Hook for managing conversations
 *
 * @example
 * ```tsx
 * function ConversationList() {
 *   const { conversations, currentId, create, select, remove } = useConversations();
 *
 *   return (
 *     <div>
 *       <button onClick={() => create()}>New Chat</button>
 *       {conversations.map((conv) => (
 *         <div
 *           key={conv.id}
 *           onClick={() => select(conv.id)}
 *           style={{ fontWeight: conv.id === currentId ? 'bold' : 'normal' }}
 *         >
 *           {conv.id}
 *           <button onClick={() => remove(conv.id)}>Delete</button>
 *         </div>
 *       ))}
 *     </div>
 *   );
 * }
 * ```
 */
export function useConversations(): UseConversationsReturn {
  const store = useStore();

  const conversationsMap = useZustand(store, selectConversations);
  const chat = useZustand(store, selectChat);

  /**
   * Convert Map to sorted array
   */
  const conversations = useMemo(() => {
    return Array.from(conversationsMap.values()).sort(
      (a, b) => b.updatedAt.getTime() - a.updatedAt.getTime()
    );
  }, [conversationsMap]);

  /**
   * Create a new conversation
   */
  const create = useCallback(
    (id?: string): string => {
      const conversationId = id ?? generateId();
      store.getState().createConversation(conversationId);
      store.getState().setConversation(conversationId);
      return conversationId;
    },
    [store]
  );

  /**
   * Switch to a conversation
   */
  const select = useCallback(
    (id: string) => {
      if (conversationsMap.has(id)) {
        store.getState().setConversation(id);
      } else {
        console.warn(`Conversation "${id}" not found`);
      }
    },
    [store, conversationsMap]
  );

  /**
   * Delete a conversation
   */
  const remove = useCallback(
    (id: string) => {
      store.getState().deleteConversation(id);
    },
    [store]
  );

  /**
   * Check if conversation exists
   */
  const has = useCallback(
    (id: string): boolean => {
      return conversationsMap.has(id);
    },
    [conversationsMap]
  );

  /**
   * Get conversation by ID
   */
  const get = useCallback(
    (id: string): Conversation | undefined => {
      return conversationsMap.get(id);
    },
    [conversationsMap]
  );

  return {
    conversations,
    currentId: chat.conversationId,
    create,
    select,
    remove,
    has,
    get,
  };
}
