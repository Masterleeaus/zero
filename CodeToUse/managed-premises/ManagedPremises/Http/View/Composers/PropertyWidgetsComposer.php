<?php
namespace Modules\ManagedPremises\Http\View\Composers;

use Illuminate\View\View;
use Modules\ManagedPremises\Entities\PropertyVisit;
use Modules\ManagedPremises\Entities\PropertyInspection;

class PropertyWidgetsComposer
{
    public function compose(View $view): void
    {
        if (!function_exists('company_id') || !company_id()) return;

        $companyId = company_id();

        $view->with('pm_next_visits', PropertyVisit::query()
            ->where('company_id', $companyId)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_for')
            ->with('property')
            ->limit(10)
            ->get());

        $view->with('pm_overdue_inspections', PropertyInspection::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', 'completed')
            ->where('scheduled_for', '<', now())
            ->orderBy('scheduled_for')
            ->with('property')
            ->limit(10)
            ->get());
    }
}
