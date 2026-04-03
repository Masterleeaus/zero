<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Modules\FacilityManagement\Entities\{Building, Doc, Inspection};

class CompliancePackController extends Controller
{
    public function building($id)
    {
        $building = Building::findOrFail($id);
        $docs = Doc::where('scope_type','building')->where('scope_id',$id)->orderByDesc('expires_at')->get();
        $ins  = Inspection::where('scope_type','building')->where('scope_id',$id)->orderByDesc('scheduled_at')->limit(50)->get();

        $html = view('facility::compliance.pack', compact('building','docs','ins'))->render();

        // If dompdf is available (barryvdh/laravel-dompdf), use it:
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');
            return $pdf->download('compliance-pack-building-'.$building->id.'.pdf');
        }

        // Fallback: return HTML (still exportable via browser print -> PDF)
        return response($html);
    }
}
