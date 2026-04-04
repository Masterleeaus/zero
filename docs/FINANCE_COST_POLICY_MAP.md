# Finance — Cost Policy Map

**Date:** 2026-04-04  
**Policy:** `App\Policies\JobCostAllocationPolicy`

---

## JobCostAllocationPolicy

Registered for `JobCostAllocation` model in `AuthServiceProvider`.

---

## Methods

| Method | Signature | Logic |
|--------|-----------|-------|
| `viewAny` | `(User $user): bool` | Always `true` — any authenticated user can access the index |
| `view` | `(User $user, JobCostAllocation $allocation): bool` | `user->company_id === allocation->company_id` |
| `create` | `(User $user): bool` | Role must be `admin`, `super_admin`, or `accountant` |
| `update` | `(User $user, JobCostAllocation $allocation): bool` | Same company + required role + allocation must not be posted |
| `delete` | `(User $user, JobCostAllocation $allocation): bool` | Same company + `admin`/`super_admin` role + not posted |

---

## Role Requirements

| Action | Allowed Roles |
|--------|--------------|
| View | Any authenticated user in same company |
| Create | `admin`, `super_admin`, `accountant` |
| Update | `admin`, `super_admin`, `accountant` (unposted only) |
| Delete | `admin`, `super_admin` (unposted only) |

---

## Posted Guard

Allocations that have been posted to the ledger (`posted = true`) cannot be updated or deleted. This preserves accounting integrity.

---

## Company Scope

All `view`, `update`, and `delete` checks enforce `user->company_id === allocation->company_id`. Cross-tenant access is never permitted through the policy.
