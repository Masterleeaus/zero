<?php

namespace Modules\Performance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Performance\Entities\JobPerformanceSnapshot;
use Modules\Performance\Services\JobPerformance\PerformanceScoringService;

class JobPerformanceSnapshotController extends Controller
{
    public function index(Request $request)
{
    // Consolidated: use Performance dashboard instead of a separate Job Performance page
    return redirect()->route('performance-dashboard.index', ['tab' => 'job-performance']);
}

    public function show($id)
    {
        $snapshot = JobPerformanceSnapshot::with(['qualityMetrics','safetyMetrics'])->findOrFail($id);
        return view('performance::job-performance.show', compact('snapshot'));
    }

    public function rescore($id, PerformanceScoringService $scoring)
    {
        $snapshot = JobPerformanceSnapshot::findOrFail($id);
        $scoring->score($snapshot);
        return redirect()->back()->with('status', __('performance::job_performance.rescore') . ' OK');
    }

    public function signoff($id)
    {
        $snapshot = JobPerformanceSnapshot::findOrFail($id);
        $snapshot->status = 'signed_off';
        $snapshot->signed_off_at = now();
        $snapshot->save();

        return redirect()->back()->with('status', __('performance::job_performance.sign_off') . ' OK');
    }
}
