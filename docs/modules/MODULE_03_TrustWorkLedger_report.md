# MODULE 03 — TrustWorkLedger: Evidence Chain Execution Layer

## Status: INSTALLED

**Installed:** 2026-04-03

---

## Summary

MODULE 03 builds the **TrustWorkLedger** — an immutable, append-only, cryptographically-chained ledger recording every meaningful execution event across service jobs, inspections, and asset service events. Every entry links to a parent hash, creating a tamper-evident chain suitable for compliance, dispute resolution, and client-facing proof of work.

---

## Artifacts Delivered

### Migration
| File | Tables Created |
|------|---------------|
| `database/migrations/2026_04_03_900100_create_trust_ledger_tables.php` | `trust_ledger_entries`, `trust_evidence_attachments`, `trust_chain_seals` |

### Models
| Class | Table | Notes |
|-------|-------|-------|
| `App\Models\Trust\TrustLedgerEntry` | `trust_ledger_entries` | Immutable — `save()` throws `ImmutableRecordException` if entry is dirty and already persisted |
| `App\Models\Trust\TrustEvidenceAttachment` | `trust_evidence_attachments` | SHA-256 checksum captured at upload time |
| `App\Models\Trust\TrustChainSeal` | `trust_chain_seals` | Periodic snapshot of the full chain state |

### Exception
| Class | Purpose |
|-------|---------|
| `App\Exceptions\Trust\ImmutableRecordException` | Thrown when attempting to mutate a persisted ledger entry |

### Services
| Class | Key Methods |
|-------|-------------|
| `App\Services\Trust\TrustLedgerService` | `record()`, `buildChainHash()`, `attachEvidence()`, `getChain()`, `verifyChain()`, `verifyEntry()`, `sealChain()` |
| `App\Services\Trust\TrustVerificationService` | `verifyEntry()`, `detectTampering()`, `generateComplianceProof()` |

### Events
| Class | Trigger |
|-------|---------|
| `App\Events\Trust\LedgerEntryRecorded` | Fired after every successful `record()` call |
| `App\Events\Trust\ChainTamperingDetected` | Fired when `detectTampering()` finds corrupted entries |
| `App\Events\Trust\ChainSealed` | Fired after a successful `sealChain()` call |

### Listeners
| Class | Listens To |
|-------|-----------|
| `App\Listeners\Trust\RecordJobCompletionOnLedger` | `App\Events\Work\JobCompleted` |
| `App\Listeners\Trust\RecordInspectionResultOnLedger` | `App\Events\Inspection\InspectionCompleted`, `InspectionFailed` |
| `App\Listeners\Trust\RecordAssetServiceOnLedger` | Called directly (no event bus wiring yet — pending `AssetServiced` event) |

### Controller & Routes
| Class | Route prefix | Named routes |
|-------|-------------|-------------|
| `App\Http\Controllers\Trust\TrustLedgerController` | `dashboard/trust` | `dashboard.trust.chain`, `dashboard.trust.verify`, `dashboard.trust.proof`, `dashboard.trust.seal` |

### Tests
| File | Type | Coverage |
|------|------|---------|
| `tests/Unit/Services/Trust/TrustLedgerServiceTest.php` | Unit | `record()`, `buildChainHash()`, `getChain()`, `verifyChain()`, `verifyEntry()`, `sealChain()`, immutability guard |
| `tests/Unit/Services/Trust/TrustVerificationServiceTest.php` | Unit | `verifyEntry()`, `detectTampering()`, `generateComplianceProof()` |
| `tests/Feature/Trust/TrustLedgerControllerTest.php` | Feature | `chain`, `verify`, `proof`, `seal` endpoints, auth guard, subject type whitelist |

---

## Architecture Notes

### Immutability
`TrustLedgerEntry::save()` overrides the Eloquent base to throw `ImmutableRecordException` if `$this->exists && $this->isDirty()`. Direct DB manipulation (e.g., in tests) is possible but logged separately.

### Chain Hash Algorithm
SHA-256 over the pipe-delimited concatenation of:
```
parent_hash | entry_type | subject_type | subject_id | actor_id | json_payload | signed_at
```
This binds every entry to the one before it, making any tampering visible.

### Race Condition Prevention
`record()` wraps everything in a `DB::transaction()` with a `lockForUpdate()` on the latest entry row, preventing concurrent inserts from using the same `parent_hash`.

### Relationship to AuditTrail
`AuditTrail` (`app/Titan/Signals/AuditTrail.php`) records internal system/AI process audit events for operational debugging. `TrustWorkLedger` records **client-facing proof-of-work events** for compliance. They are complementary and must not be merged.

---

## FSM Module Status
`trust_work_ledger` set to `installed` in `fsm_module_status.json`.
