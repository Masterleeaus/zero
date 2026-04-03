# DOC 36 — Documentation Split Strategy

Canonical documentation split:

docs/
- architecture docs
- migration docs
- product/system docs
- mode docs
- ownership maps
- lifecycle docs

.github/
- agent operating instructions
- issue templates
- PR templates
- conversion checklists
- execution prompts
- contribution workflow docs

Rules:
1. Do not blindly move all docs into .github/.
2. docs/ remains the canonical project documentation home.
3. .github/ stores agent-facing operational guidance and templates.
4. Cross-link both locations clearly.
