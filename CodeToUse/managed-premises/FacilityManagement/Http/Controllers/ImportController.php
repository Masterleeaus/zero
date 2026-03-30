<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Services\CsvImporter;

class ImportController extends Controller
{
    public function form()
    {
        return view('facility::import.form');
    }

    public function upload(Request $r, CsvImporter $importer)
    {
        $r->validate([
            'entity' => 'required|string|in:sites,buildings,unit_types,units,assets',
            'file'   => 'required|file|mimes:csv,txt',
        ]);
        $path = $r->file('file')->getRealPath();
        $res = $importer->import($r->input('entity'), $path);
        return back()->with('status', 'Imported: '.$res['imported'].'; Errors: '.count($res['errors']));
    }
}
