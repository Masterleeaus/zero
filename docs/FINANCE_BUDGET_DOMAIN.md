# Finance Budget Domain

Models: `Budget`, `BudgetLine`
Tables: `budgets`, `budget_lines`

Budget tracks company planning periods (monthly/quarterly/yearly).
BudgetLines hold per-type (revenue/expense/labor/materials/overhead/capex/liability) targets.
Status lifecycle: draft → active → archived.
