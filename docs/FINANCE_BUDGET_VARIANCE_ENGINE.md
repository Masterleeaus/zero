# Budget Variance Engine

Service: `BudgetVarianceService`

Compares budgeted amounts against actuals per line type:
- revenue: paid invoices (issue_date in period)
- labor: payroll total_gross (period_start in period)
- materials: supplier bills total_amount (approved/paid/draft)
- expense/overhead/capex: expense table amount

Returns per-line variance %, risk classification (on_track/low/medium/high/critical), and company-level summary.
