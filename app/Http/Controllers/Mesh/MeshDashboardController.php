<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mesh;

use App\Http\Controllers\Core\CoreController;
use App\Models\Mesh\MeshDispatchRequest;
use App\Models\Mesh\MeshNode;
use App\Models\Mesh\MeshSettlement;
use App\Models\Mesh\MeshTrustEvent;
use App\Services\Mesh\MeshRegistryService;
use App\Services\Mesh\MeshSettlementService;
use App\Services\Mesh\MeshTrustService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeshDashboardController extends CoreController
{
    public function __construct(
        private readonly MeshRegistryService  $registry,
        private readonly MeshTrustService     $trust,
        private readonly MeshSettlementService $settlements,
    ) {}

    /**
     * GET /dashboard/mesh/nodes
     */
    public function nodes(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $nodes     = MeshNode::forCompany($companyId)->active()->get();

        return $this->placeholder(
            'TitanMesh — Nodes',
            count($nodes) . ' active peer nodes registered',
        );
    }

    /**
     * GET /dashboard/mesh/requests
     */
    public function requests(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $requests = MeshDispatchRequest::withoutGlobalScope('company')
            ->where(function ($q) use ($companyId) {
                $q->where('requesting_company_id', $companyId)
                  ->orWhere('fulfilling_company_id', $companyId);
            })
            ->orderByDesc('created_at')
            ->paginate(25);

        return $this->placeholder(
            'TitanMesh — Dispatch Requests',
            $requests->total() . ' total mesh requests',
        );
    }

    /**
     * GET /dashboard/mesh/settlements
     */
    public function settlements(Request $request): View
    {
        $companyId   = $request->user()->company_id;
        $settlements = $this->settlements->getPendingSettlements($companyId);

        return $this->placeholder(
            'TitanMesh — Settlements',
            $settlements->count() . ' pending settlements',
        );
    }

    /**
     * GET /dashboard/mesh/trust
     */
    public function trust(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $nodes = MeshNode::forCompany($companyId)->get()->map(function (MeshNode $node) {
            return [
                'node'        => $node,
                'trust_score' => $this->trust->computeTrustScore($node),
            ];
        });

        return $this->placeholder(
            'TitanMesh — Trust',
            $nodes->count() . ' nodes tracked',
        );
    }
}
