Middleware convergence
======================

Removed middleware was host-owned or tenant/bootstrap-specific. For MagicAI
merge, route groups should use host middleware plus a future WorkCore feature
gate only where needed. Recommended host stack:
- auth
- verified (if MagicAI uses it)
- tenant/company scope
- workcore feature gate (optional)
