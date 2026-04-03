

Titan Zero — Agent Operating Instructions

Purpose

This document defines how all agents must:
	•	scan the repository
	•	extract source bundles
	•	integrate modules
	•	write documentation
	•	avoid duplication
	•	preserve architecture consistency

Applies to:
	•	GitHub Copilot agents
	•	automation agents
	•	merge agents
	•	integration agents
	•	refactor agents
	•	schema agents
	•	PWA agents
	•	Node agents
	•	mobile agents

⸻

Rule 1 — Scan Before Acting

Before writing code, agents must deep-scan:

repo root
docs/
CodeToUse/
mobile_apps/
project sources ZIPs
zero-repo.zip
extension_library.zip
AICores.zip
FSM bundles

Never assume structure from filenames.

Always confirm by inspection.

Output:

docs/SCAN_REPORT_<area>.md


⸻

Rule 2 — zero-repo.zip Is System Authority

Latest site ZIP inside project sources:

zero-repo.zip

is canonical system truth.

Agents must:
	•	verify integrations against it
	•	detect duplicates against it
	•	extend it instead of rebuilding features

⸻

Rule 3 — Never Duplicate Existing Logic

Always follow:

reuse → extend → refactor → repair → replace

Create new files only if:
	•	feature does not exist
	•	extension impossible
	•	integration unsafe
	•	architectural isolation required

⸻

Rule 4 — Mandatory Integration Mapping

Before building anything new, agents must document:

Connects to:
Tables:
Routes:
Providers:
Services:
Signals:
UI surfaces:

Stored in:

docs/MERGE_MAP_<feature>.md


⸻

Rule 5 — CodeToUse Folder Architecture

All ZIP bundles must be extracted and sorted into domains.

Target structure:

CodeToUse/

AI/
Comms/
CRM/
Dispatch/
Extensions/
Finance/
FSM/
Jobs/
Lifecycle/
Mobile/
Nexus/
Node/
Omni/
PWA/
Routing/
Scheduling/
Signals/
Tenancy/
UI/
Utilities/
Voice/
WorkCore/

If a new domain appears, create a new folder.

⸻

Rule 6 — ZIP Handling Policy

Agents must always:

extract → classify → relocate → delete archive

Never leave unopened ZIP files.

⸻

Rule 7 — Node vs PWA Separation

Node layer:

tz_node_*
mesh sync
peer routing
device identity
edge compute
federated git logic

Stored in:

CodeToUse/Node/

PWA layer:

tz_pwa_*
service workers
offline storage
runtime caching
manifest logic
client persistence

Stored in:

CodeToUse/PWA/

Never mix Node with PWA.

⸻

Rule 8 — Mobile Domain Separation

Mobile apps belong only in:

CodeToUse/Mobile/

Includes:

TitanCommand
TitanGo
TitanPortal
TitanMoney
TitanPro

Never place mobile logic inside Node or PWA domains.

⸻

Rule 9 — Documentation Structure Policy

The docs/ directory is self-organising.

Agents create folders only when:

≥ 3 documents share the same topic

Examples:

docs/pwa/
docs/fsm/
docs/comms/
docs/schema/
docs/routes/
docs/mobile/
docs/ai/
docs/merge/

Otherwise keep files at root.

⸻

Rule 10 — Documentation Index Required

Agents must maintain:

docs/DOC_INDEX.md

Each document entry must include:

Doc name
Purpose
Domain
Updated by
Date


⸻

Rule 11 — Agent Execution Docs Mirror

Execution-critical docs must also exist inside:

.github/agent-docs/

Examples:

copilot-instructions.md
merge-playbook.md
scan-rules.md
route-map.md
schema-map.md
pwa-build-plan.md

Architecture docs remain in /docs.

⸻

Rule 12 — Duplicate Detection Pass Required

Agents must compare:

zero-repo.zip
CodeToUse/
extension_library.zip
AICores.zip
FSM bundles
mobile_apps/

Output:

docs/DUPLICATE_CODE_MAP.md

Include:

already integrated modules
partial overlaps
conflicting versions
safe removals
reuse candidates


⸻

Rule 13 — Domain Classification Map Required

Agents must generate:

docs/DOMAIN_CLASSIFICATION_MAP.md

Mapping:

feature → domain
module → destination folder
tables → subsystem
providers → integration targets


⸻

Rule 14 — Feature Extraction Doctrine

Remove duplicated infrastructure during merges:

auth
roles
permissions
users table
core configs
middleware
generic providers
queue config
mail config
asset pipelines

Keep only:

feature-specific logic

Example:

Jobs
Schedules
FSM routing
Dispatch engines
AI tools
Node mesh
Offline sync
Lifecycle automation


⸻

Rule 15 — Required Output Documents Per Task

Scan task:

SCAN_REPORT_<area>.md

Build task:

BUILD_PLAN_<area>.md

Merge task:

MERGE_MAP_<area>.md

Schema task:

TABLE_MAP_<area>.md

Route task:

ROUTE_MAP_<area>.md

Cleanup task:

DOMAIN_CLASSIFICATION_MAP.md


⸻

Rule 16 — File Creation Safety Check

Before creating any file:

Agent must check:

docs/
docs/<topic>/
.github/agent-docs/
CodeToUse/
zero-repo modules

If similar file exists:

update instead of duplicating

⸻

Rule 17 — Pass Log Required Every Run

Each agent execution must create:

docs/AGENT_PASS_<name>.md

Containing:

files edited
files reused
files avoided
integrations added
conflicts resolved
duplicates removed
open risks
next targets


⸻

Rule 18 — Table Prefix Standards

Agents must enforce:

tz_pwa_*   → client runtime storage
tz_node_*  → mesh/device sync layer

Never mix prefixes.

⸻

Rule 19 — Providers Are Integration Anchors

New subsystems must connect through:

ServiceProviders
Signals
Routes
Manifest layers
Domain engines

Never create isolated modules.

⸻

Rule 20 — Golden Rule

If Titan Zero already supports a capability:

extend it

never recreate it

⸻

If you want, next I’ll generate the automatic provider-wiring instructions doc so agents can attach newly extracted domains directly into Zero’s service container cleanly. ⚙️

⸻

