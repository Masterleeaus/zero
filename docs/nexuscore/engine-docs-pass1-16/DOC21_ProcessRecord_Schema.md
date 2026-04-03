# DOC 21 — ProcessRecord Schema

Every user action becomes a ProcessRecord before execution.

Core fields:
- process_id
- entity_type
- domain
- initiated_by
- originating_node
- current_state
- data
- context
- signal_id
- validation
- processing
- processed_entity_id
- rewind_from
- rolled_back_by
- audit

Rules:
1. Nothing executes directly.
2. Every action is first recorded.
3. Mode is attached before signal emission.
4. Sentinel authority is derived from domain.
