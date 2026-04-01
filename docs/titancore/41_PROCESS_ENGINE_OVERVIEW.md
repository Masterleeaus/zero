# Titan Process Engine Overview

Purpose:
Coordinate lifecycle transitions via signal-driven state changes.

Core pipeline:

enquiry → quote → approved → scheduled → service_job → completed → invoiced → paid → retention

Each transition emits a canonical signal envelope.
