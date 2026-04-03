<?php

namespace App\Http\Controllers\Admin\TitanCore;

use App\Http\Controllers\Controller;
use App\Services\Credits\CreditsService;
use App\Titan\Signals\AuditTrail;
use App\TitanCore\Zero\AI\TitanAIRouter;
use App\TitanCore\Zero\CoreKernel;
use App\TitanCore\Zylos\ZylosBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;

class TitanCoreAdminController extends Controller
{
    public function __construct(
        protected CoreKernel $kernel,
        protected TitanAIRouter $router,
        protected AuditTrail $auditTrail,
        protected CreditsService $credits,
        protected ZylosBridge $zylos,
    ) {
    }

    // ─── Phase 5.2 – Models ────────────────────────────────────────────────

    public function models(): View
    {
        $routerStatus = $this->router->status();
        $aiConfig = config('titan_ai', []);
        $intents = [
            'text.complete'     => $aiConfig['intents']['text.complete'] ?? null,
            'image.generate'    => $aiConfig['intents']['image.generate'] ?? null,
            'voice.synthesize'  => $aiConfig['intents']['voice.synthesize'] ?? null,
            'agent.task'        => $aiConfig['intents']['agent.task'] ?? null,
            'code.assist'       => $aiConfig['intents']['code.assist'] ?? null,
        ];

        return view('panel.admin.titan.core.models', compact('routerStatus', 'aiConfig', 'intents'));
    }

    public function modelsUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_text_model'    => 'nullable|string|max:100',
            'default_image_model'   => 'nullable|string|max:100',
            'intents'               => 'nullable|array',
            'intents.*'             => 'nullable|string|max:100',
        ]);

        // NOTE: Writing directly to config/titan_ai.php is intentional for simplicity.
        // On multi-server deployments, run `php artisan config:clear` after saving.
        $configPath = config_path('titan_ai.php');
        $current = file_exists($configPath) ? include $configPath : [];

        $current['default_text_model']  = $validated['default_text_model'] ?? ($current['default_text_model'] ?? null);
        $current['default_image_model'] = $validated['default_image_model'] ?? ($current['default_image_model'] ?? null);

        foreach ($validated['intents'] ?? [] as $intent => $model) {
            $current['intents'][$intent] = $model;
        }

        file_put_contents($configPath, '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($current, true) . ';' . PHP_EOL);

        return redirect()->route('admin.titan.core.models')->with('success', 'Model routing updated.');
    }

    // ─── Phase 5.3 – Signals ──────────────────────────────────────────────

    public function signals(Request $request): View
    {
        $query = DB::table('tz_signal_queue');

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }
        if ($type = $request->input('signal_type')) {
            $query->where('signal_type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('broadcast_status', $status);
        }
        if ($age = $request->input('age')) {
            $query->where('created_at', '>=', now()->subHours((int) $age));
        }

        $signals = $query->orderByDesc('created_at')->paginate(50);

        $stats = [
            'pending'  => DB::table('tz_signal_queue')->where('broadcast_status', 'pending')->count(),
            'async'    => DB::table('tz_signal_queue')->where('broadcast_status', 'async')->count(),
            'awaiting' => DB::table('tz_signal_queue')->where('broadcast_status', 'awaiting_approval')->count(),
            'failed'   => DB::table('tz_signal_queue')->where('broadcast_status', 'failed')->count(),
            'retry'    => DB::table('tz_signal_queue')->where('retry_count', '>', 0)->count(),
        ];

        return view('panel.admin.titan.core.signals', compact('signals', 'stats'));
    }

    // ─── Phase 5.4 – Memory ───────────────────────────────────────────────

    public function memory(): View
    {
        $tables = ['tz_ai_memories', 'tz_ai_memory_embeddings', 'tz_ai_memory_snapshots', 'tz_ai_session_handoffs'];

        $stats = [];
        foreach ($tables as $table) {
            try {
                $stats[$table] = DB::table($table)->count();
            } catch (\Throwable) {
                $stats[$table] = 'n/a';
            }
        }

        $importanceDist = [];
        try {
            $importanceDist = DB::table('tz_ai_memories')
                ->selectRaw('FLOOR(importance_score * 10) / 10 as bucket, COUNT(*) as cnt')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        } catch (\Throwable) {
        }

        $expirySoon = [];
        try {
            $expirySoon = DB::table('tz_ai_memories')
                ->where('expires_at', '<=', now()->addDays(7))
                ->where('expires_at', '>', now())
                ->count();
        } catch (\Throwable) {
        }

        return view('panel.admin.titan.core.memory', compact('stats', 'importanceDist', 'expirySoon'));
    }

    public function memoryPurge(): RedirectResponse
    {
        try {
            DB::table('tz_ai_memories')->where('expires_at', '<=', now())->delete();
        } catch (\Throwable) {
        }

        return redirect()->route('admin.titan.core.memory')->with('success', 'Expired memory entries purged.');
    }

    public function memorySummarise(): RedirectResponse
    {
        try {
            Artisan::call('titan:memory:summarise');
        } catch (\Throwable) {
        }

        return redirect()->route('admin.titan.core.memory')->with('success', 'Session summarisation queued.');
    }

    // ─── Phase 5.5 – Skills ───────────────────────────────────────────────

    public function skills(): View
    {
        $skillStatus = $this->zylos->status();

        return view('panel.admin.titan.core.skills', compact('skillStatus'));
    }

    public function skillRestart(Request $request): JsonResponse
    {
        $skill = $request->input('skill');
        $result = $this->zylos->restart((string) $skill);

        return response()->json($result);
    }

    public function skillDisable(Request $request): JsonResponse
    {
        $skill = $request->input('skill');
        $result = $this->zylos->disable((string) $skill);

        return response()->json($result);
    }

    // ─── Phase 5.6 – Activity ─────────────────────────────────────────────

    public function activity(): View
    {
        $recent = [];
        try {
            $recent = DB::table('tz_audit_log')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        } catch (\Throwable) {
        }

        return view('panel.admin.titan.core.activity', compact('recent'));
    }

    // ─── Phase 5.7 – Budgets ──────────────────────────────────────────────

    public function budgets(): View
    {
        $budgetsConfig = config('titan_budgets', []);

        return view('panel.admin.titan.core.budgets', compact('budgetsConfig'));
    }

    public function budgetsUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'per_user_daily'    => 'nullable|integer|min:0',
            'per_company_daily' => 'nullable|integer|min:0',
            'per_request_max'   => 'nullable|integer|min:0',
            'daily_limit'       => 'nullable|integer|min:0',
            'intents'           => 'nullable|array',
            'intents.*'         => 'nullable|integer|min:0',
        ]);

        // NOTE: Writing directly to config/titan_budgets.php is intentional for simplicity.
        // On multi-server deployments, run `php artisan config:clear` after saving.
        $configPath = config_path('titan_budgets.php');
        $current = file_exists($configPath) ? include $configPath : [];

        foreach (['per_user_daily', 'per_company_daily', 'per_request_max', 'daily_limit'] as $key) {
            if (isset($validated[$key])) {
                $current[$key] = (int) $validated[$key];
            }
        }

        foreach ($validated['intents'] ?? [] as $intent => $cap) {
            $current['intents'][$intent] = (int) $cap;
        }

        file_put_contents($configPath, '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($current, true) . ';' . PHP_EOL);

        return redirect()->route('admin.titan.core.budgets')->with('success', 'Budget limits updated.');
    }

    // ─── Phase 5.8 – Queues ───────────────────────────────────────────────

    public function queues(): View
    {
        $queues = ['titan-ai', 'titan-signals', 'titan-skills', 'default'];
        $stats  = [];

        foreach ($queues as $q) {
            try {
                $pending = DB::table('jobs')->where('queue', $q)->count();
                $failed  = DB::table('failed_jobs')->where('queue', $q)->count();
            } catch (\Throwable) {
                $pending = 0;
                $failed  = 0;
            }
            $stats[$q] = ['pending' => $pending, 'failed' => $failed];
        }

        return view('panel.admin.titan.core.queues', compact('queues', 'stats'));
    }

    public function queueRetryFailed(Request $request): RedirectResponse
    {
        $queue = $request->input('queue', 'default');
        Artisan::call('queue:retry', ['--queue' => $queue]);

        return redirect()->route('admin.titan.core.queues')->with('success', "Retried failed jobs on [{$queue}].");
    }

    public function queueFlush(Request $request): RedirectResponse
    {
        $queue = $request->input('queue', 'default');

        try {
            DB::table('jobs')->where('queue', $queue)->delete();
        } catch (\Throwable) {
        }

        return redirect()->route('admin.titan.core.queues')->with('success', "Queue [{$queue}] flushed.");
    }

    // ─── Phase 5.9 – MCP Health ───────────────────────────────────────────

    public function health(): View
    {
        $checks = $this->runHealthChecks();

        return view('panel.admin.titan.core.health', compact('checks'));
    }

    public function healthApi(): JsonResponse
    {
        return response()->json($this->runHealthChecks());
    }

    // ─── Phase 5.14 – System Health ───────────────────────────────────────

    private function runHealthChecks(): array
    {
        $checks = [];

        // Router
        try {
            $status = $this->router->status();
            $checks['router'] = ['pass' => true, 'detail' => $status['router'] ?? 'ok'];
        } catch (\Throwable $e) {
            $checks['router'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Kernel
        try {
            $status = $this->kernel->status();
            $checks['kernel'] = ['pass' => ! empty($status), 'detail' => $status['kernel'] ?? 'ok'];
        } catch (\Throwable $e) {
            $checks['kernel'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Memory tables
        try {
            DB::table('tz_ai_memories')->limit(1)->get();
            $checks['memory_service'] = ['pass' => true, 'detail' => 'reachable'];
        } catch (\Throwable $e) {
            $checks['memory_service'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Signal pipeline
        try {
            DB::table('tz_signal_queue')->limit(1)->get();
            $checks['signal_pipeline'] = ['pass' => true, 'detail' => 'reachable'];
        } catch (\Throwable $e) {
            $checks['signal_pipeline'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Rewind hooks
        try {
            $rewindConfig = config('titan-rewind', []);
            $checks['rewind_hooks'] = ['pass' => ! empty($rewindConfig), 'detail' => empty($rewindConfig) ? 'config missing' : 'configured'];
        } catch (\Throwable $e) {
            $checks['rewind_hooks'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Zylos bridge
        try {
            $status = $this->zylos->status();
            $checks['zylos_bridge'] = ['pass' => true, 'detail' => $status['status'] ?? 'reachable'];
        } catch (\Throwable $e) {
            $checks['zylos_bridge'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // Queue workers (check if jobs table is accessible)
        try {
            DB::table('jobs')->limit(1)->get();
            $checks['queue_workers'] = ['pass' => true, 'detail' => 'jobs table reachable'];
        } catch (\Throwable $e) {
            $checks['queue_workers'] = ['pass' => false, 'detail' => $e->getMessage()];
        }

        // MCP HTTP transport
        $mcpUrl = config('titan_core.mcp.http_url', env('MCP_HTTP_URL', ''));
        if ($mcpUrl) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get($mcpUrl . '/health');
                $checks['mcp_http'] = ['pass' => $response->ok(), 'detail' => 'status ' . $response->status()];
            } catch (\Throwable $e) {
                $checks['mcp_http'] = ['pass' => false, 'detail' => $e->getMessage()];
            }
        } else {
            $checks['mcp_http'] = ['pass' => false, 'detail' => 'MCP_HTTP_URL not configured'];
        }

        return $checks;
    }
}
