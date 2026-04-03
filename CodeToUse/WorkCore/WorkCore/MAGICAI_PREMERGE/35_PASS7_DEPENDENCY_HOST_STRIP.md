# Pass 7 dependency/host strip

Removed repository-level dependency manifests and standalone docs that MagicAI host should own:
- composer.json
- composer.lock
- package.json
- README.md
- phpstan.neon.dist

Added domain-level file manifests and an extraction script so each WorkCore slice can now be lifted into MagicAI in controlled passes.
