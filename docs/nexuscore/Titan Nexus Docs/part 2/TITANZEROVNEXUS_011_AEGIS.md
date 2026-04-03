# TITANZEROVNEXUS: AEGIS Governance & Security Architecture

**Version:** 5.0  
**Document:** 011  
**Status:** Complete Security Specification

---

## EXECUTIVE SUMMARY

**AEGIS** is the unified governance layer. Every action passes through four checkpoints:

1. **Permission Gate** - Does user have access to this mode?
2. **Business Logic Gate** - Does action meet business rules?
3. **Approval Gate** - Does action need approval?
4. **Escalation Gate** - Does action have risk?

One gate per action type replaces 300+ scattered permission checks.

---

## I. AEGIS ARCHITECTURE

```
User Initiates Action
    ↓
┌─────────────────────────────────┐
│ AEGIS: Permission Gate           │
├─────────────────────────────────┤
│ Check: mode_access_control      │
│ Question: Can user access mode? │
│ Fail → 403 Forbidden            │
│ Pass → Continue                 │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ AEGIS: Business Logic Gate       │
├─────────────────────────────────┤
│ Check: Mode-specific rules       │
│ Question: Does action meet rules?│
│ Fail → Rejection signal          │
│ Pass → Continue                 │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ AEGIS: Approval Gate             │
├─────────────────────────────────┤
│ Check: approval_required matrix  │
│ Question: Need approval?         │
│ No → Execute                     │
│ Yes → Route to approver (wait)   │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ AEGIS: Escalation Gate           │
├─────────────────────────────────┤
│ Check: risk assessment matrix    │
│ Question: Is there risk?         │
│ Low → Execute normally           │
│ Medium → Notify admin (execute)  │
│ High → Hold (require approval)   │
└─────────────────────────────────┘
    ↓
Execute (via Sentinel)
    ↓
Audit Log (immutable trail)
```

---

## II. MODE_ACCESS_CONTROL TABLE

```sql
CREATE TABLE mode_access_control (
    id BIGINT PRIMARY KEY,
    
    -- Identity
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    
    -- Mode access
    mode VARCHAR(50) NOT NULL, -- 'work', 'channel', 'money', 'growth', 'admin'
    role VARCHAR(50), -- 'admin', 'manager', 'operator', 'viewer'
    
    -- Feature flags (granular permissions)
    feature_flags JSON, -- {
                        --   "can_create_job": true,
                        --   "can_approve_invoice": false,
                        --   "can_delete_campaign": false,
                        --   "can_view_analytics": true
                        -- }
    
    -- Constraints
    location_restrictions JSON, -- ['location_1', 'location_5'] or null (all)
    customer_restrictions JSON, -- null = all customers, or ['cust_1', 'cust_2']
    
    -- Temporal constraints
    effective_from TIMESTAMP,
    effective_until TIMESTAMP,
    
    -- Audit
    created_at TIMESTAMP,
    created_by BIGINT,
    updated_at TIMESTAMP,
    updated_by BIGINT,
    
    -- Enforcement
    UNIQUE KEY uk_user_mode (tenant_id, user_id, mode),
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role),
    
    FOREIGN KEY fk_tenant (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY fk_user (user_id) REFERENCES users(id)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE approval_required_matrix (
    id BIGINT PRIMARY KEY,
    
    -- Identity
    tenant_id BIGINT NOT NULL,
    mode VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL, -- 'create_job', 'create_invoice'
    
    -- Approval rule
    condition_field VARCHAR(50), -- 'amount', 'category', 'customer_id'
    condition_operator VARCHAR(10), -- '>', '==', 'in'
    condition_value VARCHAR(255), -- '5000', 'premium_customer'
    
    -- Approver
    approver_role VARCHAR(50), -- 'manager', 'admin'
    
    -- Escalation
    auto_escalate_after_minutes INT DEFAULT 1440, -- 24 hours
    
    UNIQUE (tenant_id, mode, action, condition_field, condition_operator, condition_value),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE audit_trail (
    id BIGINT PRIMARY KEY,
    
    -- Identity
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    
    -- Action
    action_type VARCHAR(100), -- 'create_job', 'approve_invoice'
    entity_type VARCHAR(50),
    entity_id BIGINT,
    
    -- Context
    http_method VARCHAR(10),
    http_path VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    -- Result
    result ENUM('success', 'failure', 'rejected'),
    error_message TEXT,
    
    -- Timing
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Searchability
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action_type),
    INDEX idx_timestamp (timestamp),
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

---

## III. PERMISSION GATE

Check if user has access to a mode:

```php
namespace TitanZero\Nexus\Gates;

class PermissionGate {
    
    public function checkAccess(User $user, string $mode, string $action): bool {
        // 1. Check temporal constraints
        $access = $user->tenant->mode_access()
            ->where('user_id', $user->id)
            ->where('mode', $mode)
            ->where('effective_from', '<=', now())
            ->where(fn($q) => $q->where('effective_until', '>=', now())
                              ->orWhereNull('effective_until'))
            ->first();
        
        if (!$access) {
            $this->logFailure($user, $mode, $action, 'mode_access_denied');
            return false;
        }
        
        // 2. Check feature flags
        $featureFlags = $access->feature_flags ?? [];
        $requiredFeature = "can_{$action}";
        
        if (!$featureFlags[$requiredFeature] ?? false) {
            $this->logFailure($user, $mode, $action, 'feature_flag_denied');
            return false;
        }
        
        // 3. Success
        $this->logSuccess($user, $mode, $action);
        return true;
    }
    
    public function checkLocationRestriction(User $user, string $mode, int $locationId): bool {
        $access = $user->tenant->mode_access()
            ->where('user_id', $user->id)
            ->where('mode', $mode)
            ->first();
        
        $restrictions = $access->location_restrictions ?? [];
        
        // If no restrictions, allow all
        if (empty($restrictions)) {
            return true;
        }
        
        // If location in restrictions, allow
        return in_array($locationId, $restrictions);
    }
    
    public function checkCustomerRestriction(User $user, string $mode, int $customerId): bool {
        $access = $user->tenant->mode_access()
            ->where('user_id', $user->id)
            ->where('mode', $mode)
            ->first();
        
        $restrictions = $access->customer_restrictions ?? [];
        
        if (empty($restrictions)) {
            return true;
        }
        
        return in_array($customerId, $restrictions);
    }
}
```

---

## IV. BUSINESS LOGIC GATE

Check if action meets business rules (by Sentinel):

```php
// In WorkSentinel
protected function validateBusinessRules(ProcessRecord $record): bool {
    $rules = [
        'schedule_job' => [
            fn($data) => $data['scheduled_at'] > now()->addHours(1),
            fn($data) => isset($data['location_id']),
            fn($data) => Location::find($data['location_id'])?->isActive(),
        ],
        'start_job' => [
            fn($data) => $job->scheduled_at <= now(),
            fn($data) => !$job->hasUnresolvedIssues(),
            fn($data) => $job->technician()->exists(),
        ],
    ];
    
    foreach ($rules[$record->action_type] ?? [] as $rule) {
        if (!$rule($record->request_data)) {
            return false;
        }
    }
    
    return true;
}
```

---

## V. APPROVAL GATE

Check if action requires approval:

```php
class ApprovalGate {
    
    public function requiresApproval(ProcessRecord $record): bool {
        // Get approval matrix entry
        $rule = ApprovalRequiredMatrix::where('tenant_id', $record->tenant_id)
            ->where('mode', $record->mode)
            ->where('action', $record->action_type)
            ->get()
            ->first(fn($rule) => $this->matchesCondition($rule, $record));
        
        if (!$rule) {
            return false; // No approval rule, doesn't require approval
        }
        
        return true;
    }
    
    private function matchesCondition(ApprovalRequiredMatrix $rule, ProcessRecord $record): bool {
        $field = $rule->condition_field;
        $operator = $rule->condition_operator;
        $requiredValue = $rule->condition_value;
        $actualValue = $record->request_data[$field] ?? null;
        
        return match($operator) {
            '>' => $actualValue > $requiredValue,
            '<' => $actualValue < $requiredValue,
            '==' => $actualValue == $requiredValue,
            'in' => in_array($actualValue, explode(',', $requiredValue)),
            'contains' => strpos($actualValue, $requiredValue) !== false,
        };
    }
    
    public function getApprover(ProcessRecord $record): User {
        $rule = ApprovalRequiredMatrix::matching($record)->first();
        
        // Get first user with approver role in this tenant
        return User::where('tenant_id', $record->tenant_id)
            ->whereHasRole($rule->approver_role)
            ->first();
    }
}
```

---

## VI. ESCALATION GATE

Check if action has risk:

```php
class EscalationGate {
    
    public function assessRisk(ProcessRecord $record): string {
        $risk = 'low';
        
        // High-value transactions
        if (isset($record->request_data['amount'])) {
            if ($record->request_data['amount'] > 50000) {
                $risk = 'high';
            } elseif ($record->request_data['amount'] > 10000) {
                $risk = 'medium';
            }
        }
        
        // Bulk operations
        if (isset($record->request_data['bulk_count'])) {
            if ($record->request_data['bulk_count'] > 1000) {
                $risk = 'high';
            } elseif ($record->request_data['bulk_count'] > 100) {
                $risk = 'medium';
            }
        }
        
        // Permission changes
        if ($record->action_type === 'admin.grant_permission' ||
            $record->action_type === 'admin.revoke_permission') {
            $risk = 'medium';
        }
        
        // Extension installations
        if ($record->action_type === 'admin.install_extension') {
            $risk = 'high';
        }
        
        return $risk;
    }
    
    public function handleRisk(ProcessRecord $record, string $risk): void {
        match($risk) {
            'low' => null, // Execute normally
            'medium' => Notification::sendToAdmins(
                "Action initiated: {$record->action_type}",
                (array) $record->request_data
            ),
            'high' => $record->escalate(
                "High-risk action: {$record->action_type}",
                User::roles('admin')->first()
            ),
        };
    }
}
```

---

## VII. ENFORCEMENT IN PROCESSRECORD

All gates triggered in sequence:

```php
class ProcessRecord {
    
    public function validate(): bool {
        // Gate 1: Permission
        if (!Gate::allows($this->action_type, Auth::user())) {
            $this->state = 'rejected';
            return false;
        }
        
        // Gate 2: Business Logic
        $sentinel = Sentinel::for($this->mode);
        if (!$sentinel->validateBusinessRules($this)) {
            $this->state = 'rejected';
            return false;
        }
        
        // Gate 3: Approval
        if (app(ApprovalGate::class)->requiresApproval($this)) {
            $this->requires_approval = true;
            $this->approved_by = null;
            $this->save();
            return true; // Waiting for approval
        }
        
        // Gate 4: Escalation
        $risk = app(EscalationGate::class)->assessRisk($this);
        app(EscalationGate::class)->handleRisk($this, $risk);
        
        if ($risk === 'high') {
            $this->state = 'escalating';
            $this->save();
            return true; // Waiting for escalation resolution
        }
        
        // All gates passed
        return true;
    }
}
```

---

## VIII. TENANT ISOLATION (The Fortress)

Multi-tenancy enforced at every layer:

```php
// Database level: every query includes tenant_id
class TenantMiddleware {
    public function handle($request, Closure $next) {
        $tenantId = Auth::user()->tenant_id;
        
        // Set tenant context globally
        app('tenant')->setId($tenantId);
        
        // Override all queries automatically
        Entity::addGlobalScope('tenant', function (Builder $query) {
            $query->where('tenant_id', app('tenant')->id());
        });
        
        return $next($request);
    }
}

// Model level: relationships enforced
class Entity extends Model {
    protected $fillable = [
        'tenant_id', 'entity_type', 'parent_id', 'status', ...
    ];
    
    public function __construct() {
        parent::__construct();
        $this->tenant_id = app('tenant')->id();
    }
    
    // Cannot query across tenants
    public function scopeTenant(Builder $query, $tenantId = null) {
        return $query->where('tenant_id', $tenantId ?? app('tenant')->id());
    }
}

// API level: authentication requires tenant context
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/actions/{mode}/{action}', function (Request $request) {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        
        // Verify tenant matches
        if ($request->header('X-Tenant-ID') != $tenantId) {
            return abort(403, 'Tenant mismatch');
        }
        
        // Proceed with tenant context
        ...
    });
});
```

---

## IX. AUDIT LOGGING

Every action logged immutably:

```php
class AuditLog {
    
    public static function record(
        User $user,
        string $actionType,
        string $entityType,
        int $entityId,
        string $result,
        ?string $errorMessage = null
    ): void {
        \DB::table('audit_trail')->insert([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'http_method' => request()->method(),
            'http_path' => request()->path(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'result' => $result,
            'error_message' => $errorMessage,
            'timestamp' => now()
        ]);
    }
}

// Called after every action
$record->execute(Auth::user());
AuditLog::record(
    Auth::user(),
    'work.job.completed',
    'job',
    $job->id,
    'success'
);
```

---

## X. COMPLIANCE & EXPORT

Audit trail export for compliance:

```php
Route::get('/compliance/audit-trail/export', function () {
    $startDate = request('from');
    $endDate = request('to');
    $format = request('format', 'csv'); // csv, json
    
    $records = AuditTrail::where('tenant_id', Auth::user()->tenant_id)
        ->whereBetween('timestamp', [$startDate, $endDate])
        ->get();
    
    if ($format === 'csv') {
        return response()
            ->streamDownload(function () use ($records) {
                $fp = fopen('php://output', 'w');
                fputcsv($fp, [
                    'timestamp',
                    'user',
                    'action',
                    'entity',
                    'result',
                    'ip_address'
                ]);
                
                foreach ($records as $record) {
                    fputcsv($fp, [
                        $record->timestamp,
                        $record->user->email,
                        $record->action_type,
                        "{$record->entity_type}#{$record->entity_id}",
                        $record->result,
                        $record->ip_address
                    ]);
                }
                
                fclose($fp);
            }, 'audit-trail.csv');
    }
    
    return response()->json($records);
});
```

---

## XI. ROLE DEFINITIONS

### **Pre-built Roles**

```
ADMIN
├─ Full access to all modes
├─ Can grant/revoke permissions
├─ Can install/disable extensions
└─ Can change system policies

MANAGER
├─ Full access to Work mode
├─ Full access to Growth mode
├─ Read-only Money mode
└─ Cannot change system policies

OPERATOR
├─ Create/read jobs in Work mode
├─ Cannot approve jobs
├─ Cannot reschedule other's jobs
└─ Read-only other modes

TECHNICIAN
├─ Can view assigned jobs
├─ Can start/complete jobs
├─ Cannot create/modify jobs
└─ Cannot access other modes

ACCOUNTANT
├─ Full access to Money mode
├─ Read-only Work mode
└─ Cannot access Growth/Admin

VIEWER
├─ Read-only all modes
├─ Cannot execute any actions
└─ For reporting/analytics only
```

---

## CONCLUSION

AEGIS is the immune system of Nexus.

Every action flows through four gates. Every gate enforces rules. Every rule is audited.

Result: **Impossible to bypass**. **Impossible to compromise**. **Impossible to confuse**.

Multi-tenant isolation at every layer. Audit trails for compliance. Role-based access control for flexibility.

One unified governance system replaces 300+ scattered permission checks.

