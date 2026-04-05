# Finance Pass 5B Policy Map

| Model | Policy | Methods |
|-------|--------|---------|
| Budget | BudgetPolicy | viewAny, view, create, update, delete |
| FinancialActionRecommendation | FinancialActionRecommendationPolicy | viewAny, view, review |

Role enforcement: admin, manager, accountant can view/create/review. Only admin can delete budgets.
Registered in AuthServiceProvider.
