# Financial Action Recommendation Model

Model: `FinancialActionRecommendation`
Table: `financial_action_recommendations`

Stores system-generated financial action recommendations for human review.
Status lifecycle: pending_review → approved | rejected | dismissed.
Fields: action_type, title, summary, reason, severity, confidence, source_service, related_type/id, payload.
