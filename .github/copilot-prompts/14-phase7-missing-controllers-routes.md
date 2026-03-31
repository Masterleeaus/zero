# Copilot Task: Phase 7 — Missing Controllers & Routes

## Context
WorkCore merge Phase 7. The primary CRUD controllers are done. These secondary/enrichment controllers need building to match the full WorkCore feature set. All must use the `CoreController` base class and `BelongsToCompany` scoping.

## Base pattern for all controllers
```php
class XController extends CoreController
{
    public function index(Request $request): View
    {
        $items = ModelX::query()
            ->with(['relatedModel'])
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.domain.x.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([...]);
        ModelX::create($data + ['company_id' => auth()->user()->company_id, 'created_by' => auth()->id()]);
        return redirect()->route('dashboard.domain.x.index')->with('status', __('Created.'));
    }

    public function update(Request $request, ModelX $x): RedirectResponse
    {
        $this->authorizeCompany($x); // ensure company_id match
        $x->update($request->validate([...]));
        return back()->with('status', __('Updated.'));
    }

    public function destroy(ModelX $x): RedirectResponse
    {
        $this->authorizeCompany($x);
        $x->delete();
        return back()->with('status', __('Deleted.'));
    }
}
```

---

## Controllers to Build

### CRM Domain (`app/Http/Controllers/Core/Crm/`)

#### 1. CustomerContactController
- Route prefix: `dashboard.crm.customers.contacts`
- Nested under Customer: `GET /customers/{customer}/contacts`, `POST /customers/{customer}/contacts`, `DELETE /contacts/{contact}`
- Views: `crm/customers/contacts/` (partial/modal embedded in customer show page)

#### 2. CustomerNoteController
- Same pattern as CustomerContactController
- Routes nested under customer show
- Note pinning: `POST /notes/{note}/pin`

#### 3. CustomerDocumentController
- Handle file upload to `storage/app/private/customer-documents/`
- Store file path, type, size in database
- Download route: `GET /customer-documents/{document}/download` → `Storage::download()`

#### 4. DealController
- Full CRUD: `dashboard.crm.deals.*`
- Status transitions: open → won / open → lost
- Filter by customer, status, date range
- Board view: group by status (kanban-style, return JSON for Alpine.js board)

#### 5. DealNoteController
- Nested under Deal, inline form on deal show page

---

### Money Domain (`app/Http/Controllers/Core/Money/`)

#### 6. CreditNoteController
- Full CRUD: `dashboard.money.credit-notes.*`
- Status transitions: draft → issued → applied / void
- `applyToInvoice(CreditNote, Invoice)`: reduce invoice balance by credit amount, update `applied_amount`

#### 7. QuoteTemplateController
- Full CRUD: `dashboard.money.quote-templates.*`
- `applyToQuote(QuoteTemplate, Quote)`: copy template items into quote line items

#### 8. BankAccountController
- Full CRUD: `dashboard.money.bank-accounts.*`
- `setDefault(BankAccount)`: unset all others for company, set this one as default

#### 9. TaxController
- Full CRUD: `dashboard.money.taxes.*`
- `setDefault(Tax)`: toggle default

---

### Team Domain (`app/Http/Controllers/Core/Team/`)

#### 10. ZoneController
- Full CRUD: `dashboard.team.zones.*`
- Simple table: name, description, color badge, active toggle

#### 11. CleanerProfileController
- Full CRUD: `dashboard.team.cleaner-profiles.*`
- Profile is 1:1 with a User
- Store documents, ABN, hire date, emergency contact

#### 12. WeeklyTimesheetController
- `index()`: list timesheets for current user's company
- `submit(WeeklyTimesheet)`: change status draft → submitted
- `approve(WeeklyTimesheet)`: submitted → approved (admin only)
- `reject(Request, WeeklyTimesheet)`: submitted → rejected with reason

---

## Route Registration
Add all routes to their respective `routes/core/*.routes.php` files.

Example for credit notes in `routes/core/money.routes.php`:
```php
Route::resource('credit-notes', CreditNoteController::class)->names('dashboard.money.credit-notes');
Route::post('credit-notes/{credit_note}/apply/{invoice}', [CreditNoteController::class, 'applyToInvoice'])
    ->name('dashboard.money.credit-notes.apply');
```

## Output Doc
Create `docs/CONTROLLER_MERGE_MATRIX.md` listing each controller, its route prefix, and completion status.
