# Source Extraction Agent Rules

`CodeToUse` contains source systems for extraction and merge.

- Treat contents as source material, not final host structure.
- Fully inventory each source package.
- Classify each file as:
  - keep
  - adapt
  - bridge
  - discard
  - defer
- Preserve original working logic first.
- Do not perform major semantic renames during extraction passes unless explicitly asked.
