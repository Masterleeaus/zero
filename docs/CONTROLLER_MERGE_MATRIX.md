## Controller Merge Matrix (Phase 7)

| Domain | Controller | Routes Added | Notes |
| --- | --- | --- | --- |
| CRM | CustomerContactController | `customers.contacts.*` | Nested under customer; placeholder UI wired for WorkCore contacts. |
| CRM | CustomerNoteController | `customers.notes.*`, `customers.notes.pin` | Nested notes with pin toggle endpoint. |
| CRM | CustomerDocumentController | `customers.documents.*`, `customers.documents.download` | Upload/download/delete customer files. |
| CRM | DealController | `deals.*`, `deals.kanban`, `deals.status` | CRUD plus kanban view and status transition hook. |
| CRM | DealNoteController | `deals.notes.*` | Nested deal notes. |
| Money | QuoteTemplateController | `quote-templates.*`, `quote-templates.apply` | Template CRUD and apply-to-quote action. |
| Money | CreditNoteController | `credit-notes.*`, `credit-notes.apply-invoice` | Credit note lifecycle with apply-to-invoice hook. |
| Money | BankAccountController | `bank-accounts.*`, `bank-accounts.set-default` | Bank account management and default setter. |
| Money | TaxController | `taxes.*`, `taxes.set-default` | Tax rate CRUD with default setter. |
| Team | ZoneController | `zones.*` | Zone CRUD for operations. |
| Team | CleanerProfileController | `cleaners.show/edit/update` | 1:1 cleaner profile routes bound to user. |
| Team | WeeklyTimesheetController | `timesheets.*`, `timesheets.submit/approve/reject` | Weekly timesheet workflow endpoints. |

All controllers extend `CoreController` and return WorkCore placeholder responses until full WorkCore assets are merged.

