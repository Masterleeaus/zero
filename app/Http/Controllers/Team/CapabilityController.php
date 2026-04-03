<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\User;
use App\Services\Team\CapabilityRegistryService;
use App\Services\Team\SkillComplianceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CapabilityController extends CoreController
{
    public function __construct(
        private readonly CapabilityRegistryService $registry,
        private readonly SkillComplianceService    $compliance,
    ) {}

    /**
     * Full capability profile for the authenticated technician.
     */
    public function profile(Request $request): View
    {
        $user    = $request->user();
        $profile = $this->registry->getSkillProfile($user);

        return $this->placeholder(
            'Capability Profile',
            'Skills, certifications and availability for ' . $user->name,
        );
    }

    /**
     * Skills list for the authenticated technician (JSON).
     */
    public function skills(Request $request): JsonResponse
    {
        $user   = $request->user();
        $skills = $user->technicianSkills()->with('skillDefinition')->get();

        return response()->json(['data' => $skills]);
    }

    /**
     * Certifications list for the authenticated technician (JSON).
     */
    public function certifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $certs = $user->certifications()->orderByDesc('issued_at')->get();

        return response()->json(['data' => $certs]);
    }

    /**
     * Availability windows and overrides for the authenticated technician (JSON).
     */
    public function availability(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'windows'   => $user->availabilityWindows()->active()->get(),
            'overrides' => $user->availabilityOverrides()->orderBy('date')->get(),
        ]);
    }

    /**
     * Compliance gaps for the authenticated user's company (JSON).
     */
    public function gaps(Request $request): JsonResponse
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not resolved.'], 422);
        }

        $report = $this->compliance->generateComplianceReport((int) $companyId);

        return response()->json($report);
    }
}
