# Release Process

## Versioning
- Semantic Versioning: `MAJOR.MINOR.PATCH`.
- Breaking changes -> MAJOR bump.

## Steps to release
1. Finish all PRs targeting `main`.
2. Update `CHANGELOG.md` (keep PR descriptions).
3. Run full test suite + CI.
4. Bump `version.txt` and tag: `vX.Y.Z`.
5. Build and publish Docker images.
6. Create GitHub Release and attach changelog notes.

## Hotfixes
- Create `hotfix/*` branch from `main`, apply fix, tag and publish.

## Release checklist
- [ ] Security scan passed
- [ ] Tests green
- [ ] Docs updated (README, QUICK_START)
- [ ] Migration notes included (if DB/schema changes)



---
## Titan Zero Lifecycle & Sync Enforcement Model

Lifecycle transitions MUST be signal-driven.

Rules:

• Lifecycle stages change only after canonical event promotion
• Offline node transitions remain draft until validated
• Pulse handlers execute post-promotion only
• Rewind governs corrections across lifecycle boundaries
• Sync transmits intent envelopes, never entity mutations

Lifecycle execution chain:

Assistant/User → ProcessRecord → Signal → Validation → Canonical Event → Pulse → Lifecycle Update
