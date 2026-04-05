<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ReorderRecommendationService;
use App\Services\Inventory\ReorderSignalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReorderController extends Controller
{
    public function __construct(
        private readonly ReorderRecommendationService $recommender,
        private readonly ReorderSignalService $signalService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $companyId       = auth()->user()->company_id;
        $recommendations = $this->recommender->generateRecommendations($companyId);

        if ($request->expectsJson()) {
            return response()->json(['recommendations' => $recommendations]);
        }

        return view('default.panel.user.inventory.reorder.index', compact('recommendations'));
    }

    public function scan(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $signals   = $this->signalService->detectLowStock($companyId);

        return response()->json(['signals' => $signals, 'count' => count($signals)]);
    }
}
