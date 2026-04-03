<?php

namespace Modules\FacilityManagement\Services;

use Illuminate\Support\Facades\DB;
use Modules\FacilityManagement\Entities\{Site,Building,UnitType,Unit,Asset};

class CsvImporter
{
    public function import(string $entity, string $path): array
    {
        $rows = $this->readCsv($path);
        $count = 0; $errors = [];
        foreach ($rows as $i=>$row) {
            try {
                switch ($entity) {
                    case 'sites': $this->upsertSite($row); break;
                    case 'buildings': $this->upsertBuilding($row); break;
                    case 'unit_types': $this->upsertUnitType($row); break;
                    case 'units': $this->upsertUnit($row); break;
                    case 'assets': $this->upsertAsset($row); break;
                    default: throw new \InvalidArgumentException('Unknown entity: '.$entity);
                }
                $count++;
            } catch (\Throwable $e) {
                $errors[] = 'Row '.($i+1).': '.$e->getMessage();
            }
        }
        return ['imported'=>$count, 'errors'=>$errors];
    }

    protected function readCsv(string $path): array
    {
        $rows = [];
        if (!is_readable($path)) return $rows;
        if (($h = fopen($path,'r')) !== false) {
            $header = null;
            while (($data = fgetcsv($h, 0, ',')) !== false) {
                if ($header === null) { $header = array_map('trim', $data); continue; }
                $row = [];
                foreach ($header as $idx=>$key) { $row[$key] = $data[$idx] ?? null; }
                $rows[] = $row;
            }
            fclose($h);
        }
        return $rows;
    }

    protected function upsertSite(array $row): void
    {
        $code = $row['code'] ?? null;
        if (!$code) throw new \RuntimeException('Missing code');
        \Modules\FacilityManagement\Entities\Site::updateOrCreate(['code'=>$code], [
            'name'=>$row['name'] ?? $code,
            'address'=>$row['address'] ?? null,
        ]);
    }

    protected function upsertBuilding(array $row): void
    {
        $code = $row['code'] ?? null;
        if (!$code) throw new \RuntimeException('Missing code');
        $siteCode = $row['site_code'] ?? null;
        $site = $siteCode ? \Modules\FacilityManagement\Entities\Site::where('code',$siteCode)->first() : null;
        \Modules\FacilityManagement\Entities\Building::updateOrCreate(['code'=>$code], [
            'site_id'=>$site->id ?? null,
            'name'=>$row['name'] ?? $code,
            'address'=>$row['address'] ?? null,
        ]);
    }

    protected function upsertUnitType(array $row): void
    {
        $code = $row['code'] ?? null;
        if (!$code) throw new \RuntimeException('Missing code');
        \Modules\FacilityManagement\Entities\UnitType::updateOrCreate(['code'=>$code], [
            'name'=>$row['name'] ?? $code,
            'description'=>$row['description'] ?? null,
        ]);
    }

    protected function upsertUnit(array $row): void
    {
        $code = $row['code'] ?? null;
        if (!$code) throw new \RuntimeException('Missing code');
        $bCode = $row['building_code'] ?? null;
        $tCode = $row['unit_type_code'] ?? null;
        $building = $bCode ? \Modules\FacilityManagement\Entities\Building::where('code',$bCode)->first() : null;
        $type = $tCode ? \Modules\FacilityManagement\Entities\UnitType::where('code',$tCode)->first() : null;
        \Modules\FacilityManagement\Entities\Unit::updateOrCreate(['code'=>$code], [
            'building_id'=>$building->id ?? null,
            'unit_type_id'=>$type->id ?? null,
            'name'=>$row['name'] ?? $code,
            'floor'=>$row['floor'] ?? null,
            'status'=>$row['status'] ?? 'active',
        ]);
    }

    protected function upsertAsset(array $row): void
    {
        $label = $row['label'] ?? null;
        if (!$label) throw new \RuntimeException('Missing label');
        $uCode = $row['unit_code'] ?? null;
        $unit = $uCode ? \Modules\FacilityManagement\Entities\Unit::where('code',$uCode)->first() : null;
        \Modules\FacilityManagement\Entities\Asset::updateOrCreate(['label'=>$label], [
            'unit_id'=>$unit->id ?? null,
            'asset_type'=>$row['asset_type'] ?? 'generic',
            'serial_no'=>$row['serial_no'] ?? null,
            'status'=>$row['status'] ?? 'active',
        ]);
    }
}
