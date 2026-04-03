<?php
namespace Modules\PropertyManagement\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\PropertyManagement\Http\Requests\CalendarFeedRequest;
use Modules\PropertyManagement\Entities\PropertyVisit;
use Modules\PropertyManagement\Entities\PropertyInspection;
use Modules\PropertyManagement\Http\Resources\CalendarItemResource;

class CalendarFeedController extends Controller
{
    public function index(CalendarFeedRequest $request)
    {
        abort_unless(function_exists('company_id'), 403);
        $companyId = company_id();

        $from = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->get('to') ?: now()->endOfMonth()->toDateString();

        $visits = PropertyVisit::query()
            ->where('company_id', $companyId)
            ->whereBetween('scheduled_for', [$from, $to])
            ->with('property')
            ->get()
            ->map(fn($v) => [
                'type' => 'visit',
                'id' => $v->id,
                'property_id' => $v->property_id,
                'title' => ($v->visit_type ?: 'Visit') . ' — ' . ($v->property?->name ?? ('#'.$v->property_id)),
                'start' => optional($v->scheduled_for)->toIso8601String(),
                'status' => $v->status,
            ]);

        $inspections = PropertyInspection::query()
            ->where('company_id', $companyId)
            ->whereBetween('scheduled_for', [$from, $to])
            ->with('property')
            ->get()
            ->map(fn($i) => [
                'type' => 'inspection',
                'id' => $i->id,
                'property_id' => $i->property_id,
                'title' => ($i->inspection_type ?: 'Inspection') . ' — ' . ($i->property?->name ?? ('#'.$i->property_id)),
                'start' => optional($i->scheduled_for)->toIso8601String(),
                'status' => $i->status,
            ]);

        return CalendarItemResource::collection($visits->merge($inspections)->values());
    }
}
