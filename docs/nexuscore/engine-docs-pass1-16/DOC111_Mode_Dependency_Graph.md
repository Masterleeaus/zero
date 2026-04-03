# Mode Dependency Graph

## Dependencies

Jobs Mode
depends on:
- entity registry
- signals spine
- routing spine

Comms Mode
depends on:
- routing spine
- assistant hooks
- signals spine

Finance Mode
depends on:
- entity registry
- approval gates
- ProcessRecord lifecycle

Admin Mode
depends on:
- AEGIS checkpoints
- permission registry
- extension installer

Social Mode
depends on:
- campaign grammar lock
- analytics emitters
