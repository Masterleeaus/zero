<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\FacilityManagement\Entities\{Site,Building,UnitType,Unit,Asset,Inspection,Doc,Meter,MeterRead,Occupancy};

class ExportController extends Controller
{
    public function csv($entity)
    {
        $map = [
            'sites' => [Site::class, ['id','tenant_id','code','name','address','created_at','updated_at']],
            'buildings' => [Building::class, ['id','tenant_id','site_id','code','name','address','created_at','updated_at']],
            'unit_types' => [UnitType::class, ['id','tenant_id','code','name','description','created_at','updated_at']],
            'units' => [Unit::class, ['id','tenant_id','building_id','unit_type_id','code','name','floor','status','created_at','updated_at']],
            'assets' => [Asset::class, ['id','tenant_id','unit_id','asset_type','label','serial_no','status','installed_at','next_service_at','created_at','updated_at']],
            'inspections' => [Inspection::class, ['id','tenant_id','scope_type','scope_id','inspector_id','status','scheduled_at','completed_at','created_at','updated_at']],
            'docs' => [Doc::class, ['id','tenant_id','scope_type','scope_id','doc_type','path','issued_at','expires_at','status','created_at','updated_at']],
            'meters' => [Meter::class, ['id','tenant_id','unit_id','asset_id','meter_type','barcode','last_reading','last_read_at','created_at','updated_at']],
            'reads' => [MeterRead::class, ['id','tenant_id','meter_id','reading','read_at','reader_id','source','created_at','updated_at']],
            'occupancy' => [Occupancy::class, ['id','tenant_id','unit_id','tenant_type','tenant_id','start_date','end_date','status','contract_ref','created_at','updated_at']],
        ];
        if (!isset($map[$entity])) abort(404);

        [$model, $cols] = $map[$entity];
        $filename = $entity.'-export-'.date('Ymd_His').'.csv';

        $response = new StreamedResponse(function () use ($model, $cols) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $cols);
            foreach ($model::query()->orderBy('id')->cursor() as $row) {
                $data = [];
                foreach ($cols as $c) { $data[] = data_get($row, $c); }
                fputcsv($out, $data);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
