import { describe, it, expect } from 'vitest';
import { CanvasEngine } from '../engine';

describe('canvas-core', () => {
  it('should export CanvasEngine', () => {
    expect(CanvasEngine).toBeDefined();
  });
});
