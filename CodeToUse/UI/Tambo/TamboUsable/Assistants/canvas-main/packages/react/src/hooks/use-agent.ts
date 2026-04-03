/**
 * useAgent Hook
 *
 * Hook for switching and managing agents.
 */

import { useCallback, useState } from 'react';
import { useEngine } from '../context/index.js';
import type { AgentConfig } from '@memvid/canvas-core/types-only';

/**
 * useAgent return type
 */
export interface UseAgentReturn {
  /** Current agent name */
  currentAgent: string;

  /** Available agents */
  agents: AgentConfig[];

  /** Switch to a different agent */
  switchAgent: (name: string) => void;

  /** Check if agent exists */
  hasAgent: (name: string) => boolean;

  /** Get agent config by name */
  getAgent: (name: string) => AgentConfig | undefined;
}

/**
 * Hook for agent management
 *
 * @example
 * ```tsx
 * function AgentSelector() {
 *   const { currentAgent, agents, switchAgent } = useAgent();
 *
 *   return (
 *     <select
 *       value={currentAgent}
 *       onChange={(e) => switchAgent(e.target.value)}
 *     >
 *       {agents.map((agent) => (
 *         <option key={agent.name} value={agent.name}>
 *           {agent.name}
 *         </option>
 *       ))}
 *     </select>
 *   );
 * }
 * ```
 */
export function useAgent(): UseAgentReturn {
  const engine = useEngine();

  const [currentAgent, setCurrentAgent] = useState<string>(
    engine.config.defaultAgent ?? 'assistant'
  );

  /**
   * Get available agents
   */
  const agents = engine.config.agents ?? [{ name: 'assistant' }];

  /**
   * Switch to a different agent
   */
  const switchAgent = useCallback(
    (name: string) => {
      const agentExists = agents.some((a: AgentConfig) => a.name === name);
      if (agentExists) {
        setCurrentAgent(name);
      } else {
        console.warn(`Agent "${name}" not found. Available agents: ${agents.map((a: AgentConfig) => a.name).join(', ')}`);
      }
    },
    [agents]
  );

  /**
   * Check if agent exists
   */
  const hasAgent = useCallback(
    (name: string): boolean => {
      return agents.some((a: AgentConfig) => a.name === name);
    },
    [agents]
  );

  /**
   * Get agent config by name
   */
  const getAgent = useCallback(
    (name: string): AgentConfig | undefined => {
      return agents.find((a: AgentConfig) => a.name === name);
    },
    [agents]
  );

  return {
    currentAgent,
    agents,
    switchAgent,
    hasAgent,
    getAgent,
  };
}
