/**
 * Canvas Store
 *
 * Zustand store for Canvas state management.
 */

import { createStore, type StoreApi } from 'zustand';
import type { Message, Conversation, RecallResult } from '@memvid/canvas-core/types-only';

/**
 * Chat state for a conversation
 */
export interface ChatState {
  /** Current conversation ID */
  conversationId: string | null;

  /** Messages in current conversation */
  messages: Message[];

  /** Whether currently loading/streaming */
  isLoading: boolean;

  /** Whether currently streaming */
  isStreaming: boolean;

  /** Current error */
  error: string | null;

  /** Sources from last message */
  sources: RecallResult[];

  /** Input value */
  inputValue: string;
}

/**
 * Canvas store state
 */
export interface CanvasState {
  /** Chat state */
  chat: ChatState;

  /** All conversations */
  conversations: Map<string, Conversation>;

  /** UI state */
  ui: {
    sidebarOpen: boolean;
    theme: 'light' | 'dark';
  };
}

/**
 * Canvas store actions
 */
export interface CanvasActions {
  // Chat actions
  setConversation: (id: string | null) => void;
  addMessage: (message: Message) => void;
  updateMessage: (id: string, updates: Partial<Message>) => void;
  setMessages: (messages: Message[]) => void;
  setLoading: (isLoading: boolean) => void;
  setStreaming: (isStreaming: boolean) => void;
  setError: (error: string | null) => void;
  setSources: (sources: RecallResult[]) => void;
  setInputValue: (value: string) => void;
  clearChat: () => void;

  // Conversation actions
  createConversation: (id: string) => void;
  deleteConversation: (id: string) => void;

  // UI actions
  toggleSidebar: () => void;
  setTheme: (theme: 'light' | 'dark') => void;

  // Reset
  reset: () => void;
}

/**
 * Full store type
 */
export type CanvasStore = StoreApi<CanvasState & CanvasActions>;

/**
 * Initial state
 */
const initialState: CanvasState = {
  chat: {
    conversationId: null,
    messages: [],
    isLoading: false,
    isStreaming: false,
    error: null,
    sources: [],
    inputValue: '',
  },
  conversations: new Map(),
  ui: {
    sidebarOpen: true,
    theme: 'light',
  },
};

/**
 * Create Canvas store
 */
export function createCanvasStore(): CanvasStore {
  return createStore<CanvasState & CanvasActions>((set) => ({
    ...initialState,

    // Chat actions
    setConversation: (id) => {
      set((state) => ({
        chat: {
          ...state.chat,
          conversationId: id,
          messages: id ? (state.conversations.get(id)?.messages ?? []) : [],
          error: null,
          sources: [],
        },
      }));
    },

    addMessage: (message) => {
      set((state) => ({
        chat: {
          ...state.chat,
          messages: [...state.chat.messages, message],
        },
      }));
    },

    updateMessage: (id, updates) => {
      set((state) => ({
        chat: {
          ...state.chat,
          messages: state.chat.messages.map((msg) =>
            msg.id === id ? { ...msg, ...updates } : msg
          ),
        },
      }));
    },

    setMessages: (messages) => {
      set((state) => ({
        chat: {
          ...state.chat,
          messages,
        },
      }));
    },

    setLoading: (isLoading) => {
      set((state) => ({
        chat: {
          ...state.chat,
          isLoading,
        },
      }));
    },

    setStreaming: (isStreaming) => {
      set((state) => ({
        chat: {
          ...state.chat,
          isStreaming,
        },
      }));
    },

    setError: (error) => {
      set((state) => ({
        chat: {
          ...state.chat,
          error,
          isLoading: false,
          isStreaming: false,
        },
      }));
    },

    setSources: (sources) => {
      set((state) => ({
        chat: {
          ...state.chat,
          sources,
        },
      }));
    },

    setInputValue: (value) => {
      set((state) => ({
        chat: {
          ...state.chat,
          inputValue: value,
        },
      }));
    },

    clearChat: () => {
      set((state) => ({
        chat: {
          ...initialState.chat,
          conversationId: state.chat.conversationId,
        },
      }));
    },

    // Conversation actions
    createConversation: (id) => {
      set((state) => {
        const newConversations = new Map(state.conversations);
        newConversations.set(id, {
          id,
          messages: [],
          createdAt: new Date(),
          updatedAt: new Date(),
        });
        return { conversations: newConversations };
      });
    },

    deleteConversation: (id) => {
      set((state) => {
        const newConversations = new Map(state.conversations);
        newConversations.delete(id);
        return {
          conversations: newConversations,
          chat:
            state.chat.conversationId === id
              ? { ...initialState.chat }
              : state.chat,
        };
      });
    },

    // UI actions
    toggleSidebar: () => {
      set((state) => ({
        ui: {
          ...state.ui,
          sidebarOpen: !state.ui.sidebarOpen,
        },
      }));
    },

    setTheme: (theme) => {
      set((state) => ({
        ui: {
          ...state.ui,
          theme,
        },
      }));
    },

    // Reset
    reset: () => {
      set(initialState);
    },
  }));
}

/**
 * Selector helpers
 */
export const selectChat = (state: CanvasState) => state.chat;
export const selectMessages = (state: CanvasState) => state.chat.messages;
export const selectIsLoading = (state: CanvasState) => state.chat.isLoading;
export const selectIsStreaming = (state: CanvasState) => state.chat.isStreaming;
export const selectError = (state: CanvasState) => state.chat.error;
export const selectSources = (state: CanvasState) => state.chat.sources;
export const selectInputValue = (state: CanvasState) => state.chat.inputValue;
export const selectConversations = (state: CanvasState) => state.conversations;
export const selectUI = (state: CanvasState) => state.ui;
