<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Services\OrderingService;

class DocumentOrderingController extends Controller
{
    public function __construct(private OrderingService $ordering) {}

    public function documents(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $this->ordering->reorderDocuments($request->input('ids', []));
        return response()->json(['ok' => true]);
    }

    public function folders(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $this->ordering->reorderFolders($request->input('ids', []));
        return response()->json(['ok' => true]);
    }

    public function templates(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $this->ordering->reorderTemplates($request->input('ids', []));
        return response()->json(['ok' => true]);
    }
}
