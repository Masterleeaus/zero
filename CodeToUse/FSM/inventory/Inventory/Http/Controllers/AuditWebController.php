<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\InventoryAudit;

class AuditWebController extends Controller
{
    public function index(Request $r)
    {
        $q = InventoryAudit::query()->orderByDesc('id');
        if ($r->filled('action')) $q->where('action','like','%'.$r->string('action').'%');
        if ($r->filled('user_id')) $q->where('user_id',$r->integer('user_id'));
        if ($r->filled('date_from')) $q->whereDate('created_at','>=',$r->date('date_from'));
        if ($r->filled('date_to')) $q->whereDate('created_at','<=',$r->date('date_to'));
        $rows = $q->paginate(25)->withQueryString();
        return view('inventory::audit.index', compact('rows'));
    }

    public function show($id)
    {
        $row = InventoryAudit::findOrFail($id);
        return view('inventory::audit.show', compact('row'));
    }
}
