# Table Rename Safety Pattern

## Rule
Never rename in-place without shadow copy validation.

## Pattern
legacy_table → shadow_tz_table → validation → swap alias → retire legacy

## Safeguards
- checksum compare
- row-count parity
- foreign-key remap audit
