# Finance Pass 5B Route Map

All routes prefixed: `dashboard.money.*`

| Name | Method | Path | Controller |
|------|--------|------|------------|
| budgets.index | GET | /dashboard/money/budgets | BudgetController@index |
| budgets.store | POST | /dashboard/money/budgets | BudgetController@store |
| budget-variance.index | GET | /dashboard/money/budget-variance | BudgetVarianceController@index |
| scenarios.index | GET | /dashboard/money/scenarios | ScenarioSimulationController@index |
| scenarios.store | POST | /dashboard/money/scenarios | ScenarioSimulationController@store |
| recommendations.index | GET | /dashboard/money/recommendations | FinancialRecommendationController@index |
| recommendations.review | GET | /dashboard/money/recommendations/{id} | FinancialRecommendationController@review |
| recommendations.approve | POST | /dashboard/money/recommendations/{id}/approve | FinancialRecommendationController@approve |
| recommendations.reject | POST | /dashboard/money/recommendations/{id}/reject | FinancialRecommendationController@reject |
| recommendations.dismiss | POST | /dashboard/money/recommendations/{id}/dismiss | FinancialRecommendationController@dismiss |
