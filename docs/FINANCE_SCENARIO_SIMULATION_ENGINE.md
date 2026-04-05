# Scenario Simulation Engine

Service: `ScenarioSimulationService`

10 scenario types: supplier_price_increase, labor_rate_increase, staff_shortage, lower_utilization,
new_recurring_jobs, customer_churn, fuel_cost_spike, delayed_collections, reduced_scheduling, reorder_timing_change.

Does NOT mutate production data. Builds a baseline from historical data, applies scenario math, returns impact report.
