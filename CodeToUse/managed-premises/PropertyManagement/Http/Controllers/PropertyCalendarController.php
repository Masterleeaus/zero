<?php
namespace Modules\PropertyManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\PropertyManagement\Entities\PropertyVisit;
use Modules\PropertyManagement\Entities\PropertyInspection;

class PropertyCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        abort_unless(function_exists('company_id'), 403);
        $companyId = company_id();

        // Permission gate (compatible with Worksuite permission helpers)
        if (function_exists('user_can')) {
            abort_unless(user_can('propertymanagement.calendar.view') || user_can('propertymanagement.view'), 403);
        }

        $month = $request->get('month') ?: now()->format('Y-m');
        [$y,$m] = array_map('intval', explode('-', $month));
        $start = now()->setDate($y,$m,1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        $visits = PropertyVisit::query()
            ->where('company_id', $companyId)
            ->whereBetween('scheduled_for', [$start, $end])
            ->orderBy('scheduled_for')
            ->with('property')
            ->get();

        $inspections = PropertyInspection::query()
            ->where('company_id', $companyId)
            ->whereBetween('scheduled_for', [$start, $end])
            ->orderBy('scheduled_for')
            ->with('property')
            ->get();

        return view('propertymanagement::calendar.index', compact('month','start','end','visits','inspections'));
    }
}
