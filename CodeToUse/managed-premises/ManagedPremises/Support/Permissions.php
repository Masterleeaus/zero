<?php

namespace Modules\ManagedPremises\Support;

class Permissions
{
    public function all(): array
    {
        return [
            // Properties
            'managedpremises.view',
            'managedpremises.create',
            'managedpremises.update',
            'managedpremises.delete',

            // Units
            'managedpremises.units.view',
            'managedpremises.units.create',
            'managedpremises.units.delete',

            // Contacts
            'managedpremises.contacts.view',
            'managedpremises.contacts.create',
            'managedpremises.contacts.delete',

            // Jobs
            'managedpremises.jobs.view',
            'managedpremises.jobs.create',
            'managedpremises.jobs.delete',

            // Keys
            'managedpremises.keys.view',
            'managedpremises.keys.create',
            'managedpremises.keys.delete',

            // Photos
            'managedpremises.photos.view',
            'managedpremises.photos.create',
            'managedpremises.photos.delete',

            // Checklists
            'managedpremises.checklists.view',
            'managedpremises.checklists.create',
            'managedpremises.checklists.delete',

// Calendar
'managedpremises.calendar.view',

// Service Plans
'managedpremises.plans.view',
'managedpremises.plans.create',
'managedpremises.plans.update',
'managedpremises.plans.delete',

// Visits
'managedpremises.visits.view',
'managedpremises.visits.create',
'managedpremises.visits.update',
'managedpremises.visits.delete',

// Inspections
'managedpremises.inspections.view',
'managedpremises.inspections.create',
'managedpremises.inspections.update',
'managedpremises.inspections.delete',

            // Settings
            'managedpremises.settings',

            // AI helpers (Titan Zero)
            'managedpremises.ai',
            // Service Plans / Visits / Inspections / Documents / Approvals
            'managedpremises.service_plans.view',
            'managedpremises.service_plans.manage',
            'managedpremises.visits.view',
            'managedpremises.visits.manage',
            'managedpremises.inspections.view',
            'managedpremises.inspections.manage',
            'managedpremises.documents.view',
            'managedpremises.documents.manage',
            'managedpremises.approvals.view',
            'managedpremises.approvals.manage',

            // Utilities / Meter Readings
            'managedpremises.meters.view',
            'managedpremises.meters.manage',
        ];
    }
}
