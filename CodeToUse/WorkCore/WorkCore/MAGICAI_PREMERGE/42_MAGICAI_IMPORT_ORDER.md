# MagicAI import order

1. Shared primitives: enums, traits, helpers actually used by imported domains.
2. Models + migrations for one domain at a time.
3. Requests, policies/permissions conversion, observers.
4. Services, jobs, events, listeners.
5. Controllers + routes under isolated namespace.
6. Views/components/assets only after route/controller wiring works.
7. Cross-domain joins last (dashboard aggregates, finance-to-site links, CRM-to-invoice links).

Recommended first merge order:
1. crm_leads_customers
2. finance_sales
3. sites_service jobs_time
4. support_comms
5. hr_attendance_leave
6. platform_misc

Reason: CRM + finance establish customer/commercial backbone before site execution and internal HR layers.