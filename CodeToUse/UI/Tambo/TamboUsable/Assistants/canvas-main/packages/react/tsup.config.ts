import { defineConfig } from 'tsup';

export default defineConfig({
  entry: [
    'src/index.ts',
    'src/hooks/index.ts',
    'src/components-client.ts',
    'src/templates-client.ts',
  ],
  format: ['esm'],
  dts: true,
  splitting: true,
  sourcemap: true,
  clean: true,
  treeshake: true,
  minify: false,
  external: ['react', 'react-dom', '@memvid/canvas-core', '@memvid/canvas-core/types-only'],
  esbuildOptions(options) {
    options.jsx = 'automatic';
  },
});
