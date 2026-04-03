# Copilot Task: Phase 3 & 4 — Missing Models & Migrations

## Context
WorkCore merge Phases 3 & 4. The core CRUD models (Customer, Enquiry, Site, ServiceJob, Quote, Invoice, Payment, Expense, Attendance, Leave, Shift, Timelog, ServiceAgreement, Checklist) are done.

The following feature-enrichment models and their migrations are still missing from the host. These are all high-priority items from the pre-merge inventory.

## Your Task — create each model + migration in order

Follow this pattern for each model:
- `app/Models/{Domain}/{ModelName}.php` — use `BelongsToCompany` + `OwnedByUser` traits where appropriate
- `database/migrations/2026_03_30_XXXXXX_create_{table}_table.php`
- Add `$fillable` matching WorkCore source schema
- Add relationships back to parent models

---

### BATCH A — CRM Enrichment

#### 1. CustomerContact
- Table: `customer_contacts`
- Belongs to: `Customer`
- Fields: `company_id`, `customer_id`, `name`, `email`, `phone`, `role`, `is_primary`, `notes`
- Relationships: `belongsTo(Customer::class)`

#### 2. CustomerNote
- Table: `customer_notes`
- Belongs to: `Customer`, created by `User`
- Fields: `company_id`, `customer_id`, `created_by`, `note`, `is_pinned`
- Relationships: `belongsTo(Customer::class)`, `belongsTo(User::class, 'created_by')`

#### 3. CustomerDocument
- Table: `customer_documents`
- Fields: `company_id`, `customer_id`, `created_by`, `name`, `file_path`, `file_type`, `file_size`, `expires_at`

#### 4. Deal (CRM pipeline)
- Table: `deals`
- Fields: `company_id`, `created_by`, `customer_id`, `enquiry_id` (nullable), `title`, `value`, `currency`, `status` (open/won/lost), `close_date`, `notes`
- Relationships: `belongsTo(Customer::class)`, `belongsTo(Enquiry::class)`

#### 5. DealNote
- Table: `deal_notes`
- Fields: `company_id`, `deal_id`, `created_by`, `note`
- Relationships: `belongsTo(Deal::class)`

---

### BATCH B — Work Enrichment

#### 6. SubChecklist (sub-tasks under a Checklist item)
- Table: `sub_checklists`
- Fields: `company_id`, `checklist_id`, `title`, `is_complete`, `completed_by` (nullable), `completed_at` (nullable), `sort_order`
- Relationships: `belongsTo(Checklist::class)`

#### 7. SiteNote
- Table: `site_notes`
- Fields: `company_id`, `site_id`, `created_by`, `note`, `is_pinned`
- Relationships: `belongsTo(Site::class)`

#### 8. WeeklyTimesheet
- Table: `weekly_timesheets`
- Fields: `company_id`, `user_id`, `week_start_date`, `week_end_date`, `total_minutes`, `status` (draft/submitted/approved/rejected), `approved_by` (nullable), `approved_at` (nullable), `notes`
- Relationships: `belongsTo(User::class)`, `hasMany(Timelog::class, 'user_id', 'user_id')` (scoped by week)

---

### BATCH C — Money Enrichment

#### 9. CreditNote
- Table: `credit_notes`
- Fields: `company_id`, `created_by`, `customer_id`, `invoice_id` (nullable), `credit_note_number`, `status` (draft/issued/applied/void), `issue_date`, `currency`, `subtotal`, `tax`, `total`, `applied_amount`, `balance`, `notes`
- Unique index: `(company_id, credit_note_number)`
- Relationships: `belongsTo(Customer::class)`, `belongsTo(Invoice::class)`

#### 10. CreditNoteItem
- Table: `credit_note_items`
- Fields: `company_id`, `credit_note_id`, `description`, `quantity`, `unit_price`, `total`, `sort_order`

#### 11. QuoteTemplate
- Table: `quote_templates`
- Fields: `company_id`, `created_by`, `name`, `description`, `currency`, `notes`
- `hasMany(QuoteTemplateItem::class)`

#### 12. QuoteTemplateItem
- Table: `quote_template_items`
- Fields: `company_id`, `quote_template_id`, `description`, `quantity`, `unit_price`, `total`, `sort_order`

#### 13. BankAccount
- Table: `bank_accounts`
- Fields: `company_id`, `created_by`, `bank_name`, `account_name`, `account_number`, `bsb` (nullable), `swift_code` (nullable), `currency`, `is_default`, `notes`

#### 14. Tax
- Table: `taxes`
- Fields: `company_id`, `name`, `rate` (decimal 5,2), `type` (percentage/fixed), `is_default`, `is_active`
- Unique index: `(company_id, name)`

---

### BATCH D — Team Enrichment

#### 15. Zone
- Table: `zones`
- Fields: `company_id`, `name`, `description`, `color`, `is_active`

#### 16. CleanerProfile (extended cleaner info)
- Table: `cleaner_profiles`
- Fields: `company_id`, `user_id`, `employee_code`, `hire_date`, `abn` (nullable), `license_number` (nullable), `emergency_contact_name`, `emergency_contact_phone`, `notes`
- Unique index: `(company_id, user_id)`
- Relationships: `belongsTo(User::class)`

---

### After Each Batch
- Add the `hasMany` or `hasOne` reverse relationship to the parent model
- Ensure `company_id` is in `$fillable`
- Use `App\Models\Concerns\BelongsToCompany` trait (not App\Traits)
- Run `php artisan migrate` after each batch to catch errors early

### Create Output Doc
Create `docs/SCHEMA_CONSOLIDATION_MAP.md` listing each table created, its columns, and relationships.
