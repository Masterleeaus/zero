# DOC 31 — Path Rename Matrix

Purpose:
Define structural rename targets from legacy extension paths into canonical Nexus paths.

Canonical rule:
- rename in place
- rewrite references
- delete duplicates
- preserve implementation behavior
- preserve UI composition where possible

Example mapping candidates:

| Legacy Path | Candidate Canonical Path | Status |
|-------------|---------------------------|--------|
| social-media-agent/ | modes/social/agents/ OR shared/agents/ | REVIEW |
| social-media/ | modes/social/ | REVIEW |
| posts/ | modes/social/posts/ OR modes/jobs/drafts/ | MODE-DEPENDENT |
| campaigns/ | modes/social/campaigns/ OR modes/jobs/programs/ | MODE-DEPENDENT |
| calendar/ | shared/schedule/ OR mode-owned schedule surface | REVIEW |
| analytics/ | modes/social/analytics/ OR shared/insights/ | REVIEW |
| accounts/ | modes/comms/contacts/ OR modes/social/accounts/ | REVIEW |
| platforms/ | modes/social/platforms/ OR modes/comms/channels/ | REVIEW |

No path is renamed before mode ownership is confirmed.
