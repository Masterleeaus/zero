# Lifecycle Engine State Machine

Canonical pipeline:

enquiry → quote → approved → scheduled → service_job → completed → invoiced → paid → retention

Each transition emits a signal envelope.

Signals trigger:
- Pulse automations
- memory updates
- notifications
- approval gates
- AI suggestions
