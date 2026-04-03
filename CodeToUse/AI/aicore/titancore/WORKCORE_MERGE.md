
# WorkCore → MagicAI Core Merge Plan
## AI Agent Execution Specification

Author: Titan Merge Orchestrator
Target: MagicAI Core Integration
Source: WorkCore_routes_ready_for_magicai.zip
Mode: Core absorption (NOT extension install)

---

# Objective

Absorb WorkCore operational capabilities directly into the MagicAI core architecture.

This is NOT:

- an extension install
- a package wrapper
- a module mount
- a side-loaded subsystem

This IS:

a structured capability merge into native MagicAI domains.

---

# Scope of Capability Transfer

Merge the following systems into MagicAI core:

CRM
Customers
Enquiries
Sites
Service Jobs
Checklists
Scheduling
Cleaners / Workforce
Quotes
Invoices
Payments
Expenses
Insights / Reporting

Optional (triage required):

Tickets
Internal chat
Notices
Knowledgebase

---

# Non-Negotiable Architecture Rules

Follow merge order:

reuse existing files
extend existing files
refactor existing files
repair existing files
create new files ONLY if unavoidable

Never replace working MagicAI code with WorkCore equivalents.

---

# Tenant Doctrine

Use:

company_id = tenant boundary
team_id = crew grouping
user_id = actor identity

Rules:

team_id must remain
team_id must NOT isolate tenants
company_id must scope all queries

---

# Vocabulary Normalisation Rules

Translate meaning, not words.

clients → customers
leads → enquiries
projects → sites OR service_jobs
tasks → checklists OR service_jobs
employees → cleaners
estimates → quotes
finance → money
reports → insights

Context determines mapping.

---

# Phase 1 — Repository Inventory

Scan both:

MagicAI repository
WorkCore source ZIP

Extract:

models
controllers
routes
services
migrations
views
policies
middleware
language files
providers
config files

Output:

docs/CORE_MERGE_AUDIT.md

Must include:

file collisions
namespace conflicts
table conflicts
route conflicts
provider conflicts

---

# Phase 2 — Dependency Resolution

Merge composer.json safely.

Rules:

same package compatible versions → choose highest
same package incompatible versions → adapter layer
package only in WorkCore → add

Validate:

composer validate
composer install
artisan config:cache

Output:

docs/DEPENDENCY_RESOLUTION_REPORT.md

---

# Phase 3 — Database Schema Consolidation

Compare schemas.

For each table:

keep
merge
rename
replace
discard

Rules:

same table same schema → keep primary
same table different schema → ALTER migration
different FK structure → consolidate relationship

Priorities:

customers
enquiries
sites
service_jobs
checklists
quotes
invoices
payments
expenses
cleaners
attendance
shifts

Output:

docs/SCHEMA_CONSOLIDATION_MAP.md

---

# Phase 4 — Model Reconciliation

Merge duplicated models.

Combine:

relationships
fillable fields
casts
accessors
scopes

Enforce tenant scope:

company_id global filtering

Repair:

relationships
policies
query builders
dashboard queries

Output:

docs/MODEL_SCOPE_MATRIX.md

---

# Phase 5 — Service Provider Merge

Merge providers.

Resolve:

duplicate bindings
conflicting services
payment gateways
notification drivers

If binding conflict:

introduce interface abstraction

Example:

PaymentGateway interface

Select provider via config.

Output:

docs/PROVIDER_BINDING_MAP.md

---

# Phase 6 — Route Integration

Remove WorkCore standalone routing.

Create modular core routes:

routes/core/crm.routes.php
routes/core/work.routes.php
routes/core/money.routes.php
routes/core/team.routes.php
routes/core/insights.routes.php

Ensure compatibility with:

dashboard routes
admin routes
user routes
chatbot routes

Validate:

route:list
route:cache

Output:

docs/ROUTE_CONFLICT_MATRIX.md

---

# Phase 7 — Controller Merge

Compare controllers.

Merge logic where overlapping.

Separate:

CRUD
workflow
AJAX endpoints
reporting

Remove:

duplicate middleware stacks
standalone boot assumptions

Output:

docs/CONTROLLER_MERGE_MATRIX.md

---

# Phase 8 — View Absorption

Do NOT mount secondary UI.

Reuse MagicAI layouts.

Absorb:

customer views
site views
service job views
checklists
quotes
invoices
schedule
cleaners
insights dashboards

Replace standalone wrappers.

Output:

docs/VIEW_ABSORPTION_PLAN.md

---

# Phase 9 — Menu Integration

Merge navigation.

Target structure:

Connect
Work
Money
Team
Insights

Ensure:

valid route names
no duplicates
correct icons
correct permissions

Output:

docs/CORE_MENU_MAP.md

---

# Phase 10 — Workflow Normalisation

Standard lifecycle:

enquiry
quote_draft
quote_sent
quote_approved
scheduled
active
completed
invoiced
overdue
paid
recurring

Centralise transitions inside service layer.

Output:

docs/WORKFLOW_STATE_MAP.md

---

# Phase 11 — Insights Integration

Preserve analytics logic.

Prioritise:

quote conversion
job volume
completion rates
overdue invoices
cleaner utilisation
revenue per site
customer lifetime value

Rewrite vocabulary.

Output:

docs/INSIGHTS_REBUILD_PLAN.md

---

# Phase 12 — Support Feature Triage

Classify:

merge now
defer
archive

Evaluate:

tickets
internal chat
notices
knowledgebase

Avoid duplicate communication stacks.

Output:

docs/COMMS_TRIAGE_PLAN.md

---

# Phase 13 — Language File Normalisation

Scan:

validation messages
menus
notifications
labels
dashboards
buttons

Remove legacy vocabulary.

Output:

docs/LANGUAGE_NORMALISATION_PLAN.md

---

# Phase 14 — Boot Process Integration

Merge:

providers
configs
bindings
service registrations

Remove overlay dependencies.

Ensure native core boot compatibility.

Output:

docs/CORE_BOOT_INTEGRATION_PLAN.md

---

# Phase 15 — Validation

Run:

composer validate
artisan config:cache
artisan route:cache
artisan migrate --dry-run
artisan test

Confirm:

relationships valid
routes load
auth works
tenant scope correct
views render
assets compile

Output:

docs/MERGE_VALIDATION_REPORT.md

---

# Definition of Done

Merge complete when:

WorkCore functionality exists inside MagicAI core

No extension wrapper required

company_id scopes all tenants

team_id preserved as crew grouping

routes modularised

controllers merged

models unified

schema consolidated

menus integrated

views native

analytics aligned

language normalised

system boots cleanly

tests pass

no duplicate subsystems remain
