# Financial Risk Escalation Layer

Service: `FinancialRiskEscalationService`

Evaluates four risk dimensions:
1. Liquidity risk — cash runway below threshold
2. Payables pressure — outstanding supplier payables above threshold
3. Margin erosion — 30-day margin below minimum %
4. Budget overruns — active budget variance above threshold %

Each detected risk creates a FinancialActionRecommendation and dispatches a domain event.
