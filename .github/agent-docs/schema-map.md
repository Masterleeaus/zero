# Schema Consolidation Map

Phase 3 & 4 enrichment models and migrations consolidated into the TitanZero host core.

---

## CRM Domain (`app/Models/Crm/`)

| Model | Table | Parent Model | Relationship | Migration |
|-------|-------|--------------|--------------|-----------|
| `CustomerContact` | `customer_contacts` | `Customer` | `Customer::contacts()` HasMany | `2026_03_31_100000_create_customer_contacts_table.php` |
| `CustomerNote` | `customer_notes` | `Customer` | `Customer::notes()` HasMany | `2026_03_31_100100_create_customer_notes_table.php` |
| `CustomerDocument` | `customer_documents` | `Customer` | `Customer::documents()` HasMany | `2026_03_31_100200_create_customer_documents_table.php` |
| `Deal` | `deals` | `Customer` | `Customer::deals()` HasMany | `2026_03_31_100300_create_deals_table.php` |
| `DealNote` | `deal_notes` | `Deal` | `Deal::notes()` HasMany | `2026_03_31_100300_create_deals_table.php` |

### CustomerContact
- **Namespace:** `App\Models\Crm\CustomerContact`
- **Traits:** `BelongsToCompany`
- **Key fields:** `customer_id`, `name`, `email`, `phone`, `role`, `is_primary`, `notes`
- **FK:** `customer_contacts.customer_id → customers.id` (cascadeOnDelete)

### CustomerNote
- **Namespace:** `App\Models\Crm\CustomerNote`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `customer_id`, `created_by`, `body`
- **FK:** `customer_notes.customer_id → customers.id` (cascadeOnDelete)

### CustomerDocument
- **Namespace:** `App\Models\Crm\CustomerDocument`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `customer_id`, `created_by`, `name`, `file_path`, `file_size`, `mime_type`
- **FK:** `customer_documents.customer_id → customers.id` (cascadeOnDelete)

### Deal
- **Namespace:** `App\Models\Crm\Deal`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `customer_id`, `created_by`, `title`, `value`, `currency`, `status`, `stage`, `expected_close_date`, `notes`
- **FK:** `deals.customer_id → customers.id` (cascadeOnDelete)

### DealNote
- **Namespace:** `App\Models\Crm\DealNote`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `deal_id`, `created_by`, `body`
- **FK:** `deal_notes.deal_id → deals.id` (cascadeOnDelete)

---

## Work Domain (`app/Models/Work/`)

| Model | Table | Parent Model | Relationship | Migration |
|-------|-------|--------------|--------------|-----------|
| `SubChecklist` | `sub_checklists` | `Checklist` | `Checklist::subChecklists()` HasMany | `2026_03_31_100800_create_site_notes_and_sub_checklists_table.php` |
| `SiteNote` | `site_notes` | `Site` | `Site::siteNotes()` HasMany | `2026_03_31_100800_create_site_notes_and_sub_checklists_table.php` |
| `WeeklyTimesheet` | `weekly_timesheets` | `User` | `User::weeklyTimesheets()` HasMany | `2026_03_31_100800_create_site_notes_and_sub_checklists_table.php` |

### SubChecklist
- **Namespace:** `App\Models\Work\SubChecklist`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `checklist_id`, `service_job_id`, `task`, `is_completed`, `completed_at`, `completed_by`, `sort_order`
- **FK:** `sub_checklists.checklist_id → checklists.id` (cascadeOnDelete)

### SiteNote
- **Namespace:** `App\Models\Work\SiteNote`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `site_id`, `created_by`, `body`, `type`
- **FK:** `site_notes.site_id → sites.id` (cascadeOnDelete)

### WeeklyTimesheet
- **Namespace:** `App\Models\Work\WeeklyTimesheet`
- **Traits:** `BelongsToCompany`
- **Key fields:** `user_id`, `week_start`, `week_end`, `total_hours`, `status`, `approved_by`, `approved_at`
- **Unique constraint:** `(company_id, user_id, week_start)`

---

## Money Domain (`app/Models/Money/`)

| Model | Table | Parent Model | Relationship | Migration |
|-------|-------|--------------|--------------|-----------|
| `CreditNote` | `credit_notes` | `Invoice` | `Invoice::creditNotes()` HasMany | `2026_03_31_100400_create_credit_notes_table.php` |
| `CreditNoteItem` | `credit_note_items` | `CreditNote` | `CreditNote::items()` HasMany | `2026_03_31_100400_create_credit_notes_table.php` |
| `QuoteTemplate` | `quote_templates` | `User` (creator) | — | `2026_03_31_100500_create_quote_templates_table.php` |
| `QuoteTemplateItem` | `quote_template_items` | `QuoteTemplate` | `QuoteTemplate::items()` HasMany | `2026_03_31_100500_create_quote_templates_table.php` |
| `BankAccount` | `bank_accounts` | — | `BankAccount::payments()` HasMany | `2026_03_31_100600_create_bank_accounts_and_taxes_table.php` |
| `Tax` | `taxes` | — | — | `2026_03_31_100600_create_bank_accounts_and_taxes_table.php` |

### CreditNote
- **Namespace:** `App\Models\Money\CreditNote`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `customer_id`, `invoice_id`, `created_by`, `reference`, `title`, `status`, `currency`, `subtotal`, `tax_total`, `total`, `issued_at`, `notes`
- **FK:** `credit_notes.invoice_id → invoices.id` (nullable)

### CreditNoteItem
- **Namespace:** `App\Models\Money\CreditNoteItem`
- **Traits:** `BelongsToCompany`
- **Key fields:** `credit_note_id`, `description`, `quantity`, `unit_price`, `tax_rate`, `total`, `sort_order`
- **FK:** `credit_note_items.credit_note_id → credit_notes.id` (cascadeOnDelete)

### QuoteTemplate
- **Namespace:** `App\Models\Money\QuoteTemplate`
- **Traits:** `BelongsToCompany`, `OwnedByUser`
- **Key fields:** `created_by`, `name`, `title`, `currency`, `notes`, `terms`, `is_default`

### QuoteTemplateItem
- **Namespace:** `App\Models\Money\QuoteTemplateItem`
- **Traits:** `BelongsToCompany`
- **Key fields:** `quote_template_id`, `description`, `quantity`, `unit_price`, `tax_rate`, `sort_order`
- **FK:** `quote_template_items.quote_template_id → quote_templates.id` (cascadeOnDelete)

### BankAccount
- **Namespace:** `App\Models\Money\BankAccount`
- **Traits:** `BelongsToCompany`
- **Key fields:** `account_name`, `bank_name`, `account_number`, `bsb`, `currency`, `is_default`, `notes`
- **Reverse FK on Payment:** `payments.bank_account_id → bank_accounts.id` (nullable; migration `2026_03_31_100900`)

### Tax
- **Namespace:** `App\Models\Money\Tax`
- **Traits:** `BelongsToCompany`
- **Key fields:** `name`, `rate`, `is_default`, `is_compound`, `description`

---

## Team Domain (`app/Models/Team/`)

| Model | Table | Parent Model | Relationship | Migration |
|-------|-------|--------------|--------------|-----------|
| `Zone` | `zones` | — | `CleanerProfile::zone()` BelongsTo | `2026_03_31_100700_create_zones_and_cleaner_profiles_table.php` |
| `CleanerProfile` | `cleaner_profiles` | `User` | `User::cleanerProfile()` HasOne | `2026_03_31_100700_create_zones_and_cleaner_profiles_table.php` |

### Zone
- **Namespace:** `App\Models\Team\Zone`
- **Traits:** `BelongsToCompany`
- **Key fields:** `name`, `description`, `color`, `is_active`

### CleanerProfile
- **Namespace:** `App\Models\Team\CleanerProfile`
- **Traits:** `BelongsToCompany`
- **Key fields:** `user_id`, `zone_id`, `employment_type`, `hire_date`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `notes`, `is_active`
- **Unique constraint:** `user_id`
- **FK:** `cleaner_profiles.zone_id → zones.id` (nullOnDelete)

---

## Supplementary Migration

| Migration | Purpose |
|-----------|---------|
| `2026_03_31_100900_add_bank_account_id_to_payments_table.php` | Adds nullable `bank_account_id` FK to `payments` table, enabling `Payment::bankAccount()` BelongsTo |

---

## Parent Model Reverse Relationships Added

| Parent Model | Method Added | Points To |
|--------------|-------------|-----------|
| `App\Models\Crm\Customer` | `contacts()` | `CustomerContact` HasMany |
| `App\Models\Crm\Customer` | `notes()` | `CustomerNote` HasMany |
| `App\Models\Crm\Customer` | `documents()` | `CustomerDocument` HasMany |
| `App\Models\Crm\Customer` | `deals()` | `Deal` HasMany |
| `App\Models\Work\Site` | `siteNotes()` | `SiteNote` HasMany |
| `App\Models\Work\Checklist` | `subChecklists()` | `SubChecklist` HasMany |
| `App\Models\Money\Invoice` | `creditNotes()` | `CreditNote` HasMany |
| `App\Models\Money\Payment` | `bankAccount()` | `BankAccount` BelongsTo |
| `App\Models\User` | `cleanerProfile()` | `CleanerProfile` HasOne |
| `App\Models\User` | `weeklyTimesheets()` | `WeeklyTimesheet` HasMany |
