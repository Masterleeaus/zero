# Portal Merge Notes

This extension now contains the first bridge layer for a cleaning customer portal.

## Added database layer
The extension now stores its own portal-specific data while linking to WorkCore objects when available:
- site memory
- recurring service preferences
- post-job feedback / re-clean requests
- customer notifications

## Added API layer
Portal overview and notifications endpoints are registered under `api/v2/chatbot`.

## Added model layer
Portal models live beside existing chatbot models and intentionally avoid hard foreign keys to WorkCore tables so the extension remains install-safe across host systems.

## Next implementation pass
1. Add admin form fields for portal config.
2. Feed portal payload into the existing widget Home tab.
3. Add quick actions for book, reschedule, pay invoice, request re-clean.
4. Resolve customer-to-WorkCore mapping more precisely.
