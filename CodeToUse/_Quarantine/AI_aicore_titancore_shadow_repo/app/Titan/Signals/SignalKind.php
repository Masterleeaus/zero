<?php

namespace App\Titan\Signals;

final class SignalKind
{
    public const MONEY_AT_RISK     = 'money_at_risk';
    public const OVERDUE_INVOICES  = 'overdue_invoices';
    public const UNASSIGNED_JOBS   = 'unassigned_jobs';
    public const MISSING_EVIDENCE  = 'missing_evidence';
    public const CAPACITY_RISK     = 'capacity_risk';
    public const SCHEDULE_CONFLICT = 'schedule_conflict';
}
