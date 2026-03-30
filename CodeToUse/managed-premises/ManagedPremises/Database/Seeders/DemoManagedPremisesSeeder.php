<?php

namespace Modules\ManagedPremises\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoManagedPremisesSeeder extends Seeder
{
    

private function whereUser($query, string $table, $userId)
{
    if (Schema::hasColumn($table, 'user_id') && !empty($userId)) {
        $query
;
    }
    return $query;
}

private function addUser(array $data, string $table, $userId): array
{
    if (Schema::hasColumn($table, 'user_id') && !empty($userId)) {
        $data['user_id'] = $userId;
    }
    return $data;
}

public function run(): void
    {
        // Safe demo data for cleaning businesses.
        // Uses pm_* tables to preserve compatibility.
        // Idempotent inserts (checks by name + company_id + user_id where possible).

        $companies = DB::table('companies')->select('id')->get();
        foreach ($companies as $company) {
            $companyId = $company->id;

            // Pick the first admin user in this company as owner context (best-effort).
            $userId = DB::table('users')->where('company_id', $companyId)->value('id');
            if (!$userId) {
                // fallback: any user
                $userId = DB::table('users')->value('id');
            }
            // Premise (Property)
            $premiseName = 'Demo: Weekly Residential Premise';
            $premiseId = $this->whereUser(DB::table('pm_properties'), 'pm_properties', $userId)
                ->where('company_id', $companyId)
->where('name', $premiseName)
                ->value('id');

            if (!$premiseId) {
                $premiseId = $this->whereUser(DB::table('pm_properties'), 'pm_properties', $userId)->insertGetId([
                    'company_id' => $companyId,                    'name' => $premiseName,
                    'address' => '123 Demo St',
                    'suburb' => 'Demo Suburb',
                    'state' => 'NSW',
                    'postcode' => '2000',
                    'notes' => 'Use as a safe demo record. Replace with real premises.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Rooms / Units
            $rooms = ['Kitchen', 'Bathroom', 'Living Room', 'Bedroom'];
            foreach ($rooms as $room) {
                $exists = $this->whereUser(DB::table('pm_property_units'), 'pm_property_units', $userId)
                    ->where('company_id', $companyId)
->where('property_id', $premiseId)
                    ->where('name', $room)
                    ->exists();

                if (!$exists) {
                    $this->whereUser(DB::table('pm_property_units'), 'pm_property_units', $userId)->insert([
                        'company_id' => $companyId,                        'property_id' => $premiseId,
                        'name' => $room,
                        'notes' => 'Demo room/zone',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Hazards
            $hazards = ['Pets on site', 'Fragile surfaces', 'Chemical sensitivity'];
            foreach ($hazards as $hazard) {
                $exists = $this->whereUser(DB::table('pm_property_hazards'), 'pm_property_hazards', $userId)
                    ->where('company_id', $companyId)
->where('property_id', $premiseId)
                    ->where('title', $hazard)
                    ->exists();

                if (!$exists) {
                    $this->whereUser(DB::table('pm_property_hazards'), 'pm_property_hazards', $userId)->insert([
                        'company_id' => $companyId,                        'property_id' => $premiseId,
                        'title' => $hazard,
                        'severity' => 'medium',
                        'notes' => 'Demo hazard',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Keys & access
            $exists = $this->whereUser(DB::table('pm_property_keys'), 'pm_property_keys', $userId)
                ->where('company_id', $companyId)
->where('property_id', $premiseId)
                ->where('label', 'Lockbox')
                ->exists();
            if (!$exists) {
                $this->whereUser(DB::table('pm_property_keys'), 'pm_property_keys', $userId)->insert([
                    'company_id' => $companyId,                    'property_id' => $premiseId,
                    'label' => 'Lockbox',
                    'access_details' => 'Code: 1234 (demo)',
                    'notes' => 'Demo access method',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Checklist
            $checklistName = 'Demo: Standard Weekly Checklist';
            $checklistId = $this->whereUser(DB::table('pm_property_checklists'), 'pm_property_checklists', $userId)
                ->where('company_id', $companyId)
->where('property_id', $premiseId)
                ->where('name', $checklistName)
                ->value('id');
            if (!$checklistId) {
                $checklistId = $this->whereUser(DB::table('pm_property_checklists'), 'pm_property_checklists', $userId)->insertGetId([
                    'company_id' => $companyId,                    'property_id' => $premiseId,
                    'name' => $checklistName,
                    'notes' => 'Demo checklist for cleaning businesses.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Checklist items (if table exists; forks vary)
            if (DB::getSchemaBuilder()->hasTable('pm_property_checklist_items')) {
                $items = ['Wipe benches', 'Vacuum floors', 'Mop floors', 'Clean mirrors'];
                foreach ($items as $item) {
                    $exists = $this->whereUser(DB::table('pm_property_checklist_items'), 'pm_property_checklist_items', $userId)
                        ->where('company_id', $companyId)
->where('checklist_id', $checklistId)
                        ->where('title', $item)
                        ->exists();

                    if (!$exists) {
                        $this->whereUser(DB::table('pm_property_checklist_items'), 'pm_property_checklist_items', $userId)->insert([
                            'company_id' => $companyId,                            'checklist_id' => $checklistId,
                            'title' => $item,
                            'is_required' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
