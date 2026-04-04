# HRM Approval Pipelines

## Leave Approval Flow
```
Create Leave (status=pending)
  → Admin reviews
  → POST /dashboard/team/work/leaves/{leave}/approve
      → leave.status = 'approved'
      → leave.approved_by = auth user id
      → leave.approved_at = now()
      → LeaveApproved::dispatch($leave, $user)
  OR
  → POST /dashboard/team/work/leaves/{leave}/reject
      → leave.status = 'rejected'
      → leave.rejection_reason = $reason
      → LeaveRejected::dispatch($leave, $user, $reason)
```

## Guards
- `abort_if($leave->company_id !== $user->company_id, 403)` — company isolation
- `abort_if(!$user->isAdmin(), 403)` — admin only

## Events
- `LeaveApproved` — carries Leave + approver User
- `LeaveRejected` — carries Leave + rejector User + reason string
