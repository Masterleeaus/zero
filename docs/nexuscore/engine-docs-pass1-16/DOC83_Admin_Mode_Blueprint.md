# Admin Mode Blueprint

## Scope
System governance and configuration.

## Entities
User → Role → Permission → Extension → Setting → AuditEvent

## Responsibilities
- Access control
- Extension registry
- Settings management
- Audit visibility
- Governance enforcement

## Signals
permission.changed
extension.installed
setting.updated
audit.logged
