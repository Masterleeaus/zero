# MCP Tool Registry Contract

Tool naming convention:

titan.<domain>.<action>

Examples:

titan.crm.search
titan.work.schedule
titan.finance.invoice.create
titan.signal.dispatch
titan.memory.recall

Rules:

All tools must:
- pass through AIRouter if AI involved
- respect approval chains
- log execution
- remain rewind-compatible
