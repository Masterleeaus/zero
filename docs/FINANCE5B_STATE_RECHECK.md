# Finance Pass 5B — State Recheck

Confirmed model field mappings before implementation:
- `Payroll.total_gross` — period-level gross, used for labor cost queries
- `SupplierBill.total_amount` / `amount_paid` — outstanding payables computed as difference
- `Invoice.total`, `balance`, `issue_date`, `status`
- `Account` namespace: `App\Models\Money\Account`
