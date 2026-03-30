<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class WorkOrderBridgeController extends Controller
{
    public function createFromInspection($id)
    {
        // If JobManagement routes exist, redirect to a sensible create route with prefill params.
        if (Route::has('work.create')) {
            return redirect()->route('work.create', ['inspection_id' => $id]);
        }
        // Else, just return JSON stub so UI can show a message.
        return response()->json([
            'ok'=>false,
            'message'=>'JobManagement work.create route not found. Please install/enable the JobManagement module.'
        ]);
    }

    public function createFromAsset($id)
    {
        if (Route::has('work.create')) {
            return redirect()->route('work.create', ['asset_id' => $id]);
        }
        return response()->json([
            'ok'=>false,
            'message'=>'JobManagement work.create route not found. Please install/enable the JobManagement module.'
        ]);
    }
}
