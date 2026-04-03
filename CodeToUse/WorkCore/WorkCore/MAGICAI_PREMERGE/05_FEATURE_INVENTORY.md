# WorkCore Feature Inventory for MagicAI Merge

This pass keeps feature code and strips more standalone SaaS/auth/admin infrastructure in line with the pre-merge guide.

## Current retained counts
- controllers: 196
- models: 238
- services: 1
- jobs: 8
- events: 102
- listeners: 95
- views: 1381
- migrations: 274

## Controller area distribution
- root: 186
- Payment: 10

## Recommended first merge slices
- HR / attendance / leave
- Sites / service jobs / time logs
- CRM / customers / enquiries
- Service Agreements / invoices / quotes
- Issues / Support / notices / knowledge
- Payroll / expenses / payments
