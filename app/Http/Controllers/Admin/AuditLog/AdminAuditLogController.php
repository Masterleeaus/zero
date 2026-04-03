<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\AuditLog;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminAuditLogController
 *
 * Read-only viewer for the Titan tz_audit_log table, exposed in the
 * Admin panel with filtering and pagination support.
 *
 * Routes: titan.admin.audit.*
 */
class AdminAuditLogController extends Controller
{
    public function __construct(
        protected AdminAuditService $auditService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['company_id', 'action', 'performed_by', 'from', 'to']);
        $logs    = $this->auditService->paginate($filters);
        $actions = $this->auditService->distinctActions();

        return view('panel.admin.audit.index', compact('logs', 'actions', 'filters'));
    }
}
