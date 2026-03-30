<?php

namespace Modules\PropertyManagement\Support;

class Permissions
{
    public function all(): array
    {
        return [
            // Properties
            'propertymanagement.view',
            'propertymanagement.create',
            'propertymanagement.update',
            'propertymanagement.delete',

            // Units
            'propertymanagement.units.view',
            'propertymanagement.units.create',
            'propertymanagement.units.delete',

            // Contacts
            'propertymanagement.contacts.view',
            'propertymanagement.contacts.create',
            'propertymanagement.contacts.delete',

            // Jobs
            'propertymanagement.jobs.view',
            'propertymanagement.jobs.create',
            'propertymanagement.jobs.delete',

            // Keys
            'propertymanagement.keys.view',
            'propertymanagement.keys.create',
            'propertymanagement.keys.delete',

            // Photos
            'propertymanagement.photos.view',
            'propertymanagement.photos.create',
            'propertymanagement.photos.delete',

            // Checklists
            'propertymanagement.checklists.view',
            'propertymanagement.checklists.create',
            'propertymanagement.checklists.delete',

// Calendar
'propertymanagement.calendar.view',

// Service Plans
'propertymanagement.plans.view',
'propertymanagement.plans.create',
'propertymanagement.plans.update',
'propertymanagement.plans.delete',

// Visits
'propertymanagement.visits.view',
'propertymanagement.visits.create',
'propertymanagement.visits.update',
'propertymanagement.visits.delete',

// Inspections
'propertymanagement.inspections.view',
'propertymanagement.inspections.create',
'propertymanagement.inspections.update',
'propertymanagement.inspections.delete',

            // Settings
            'propertymanagement.settings',

            // AI helpers (Titan Zero)
            'propertymanagement.ai',
            // Service Plans / Visits / Inspections / Documents / Approvals
            'propertymanagement.service_plans.view',
            'propertymanagement.service_plans.manage',
            'propertymanagement.visits.view',
            'propertymanagement.visits.manage',
            'propertymanagement.inspections.view',
            'propertymanagement.inspections.manage',
            'propertymanagement.documents.view',
            'propertymanagement.documents.manage',
            'propertymanagement.approvals.view',
            'propertymanagement.approvals.manage',

            // Utilities / Meter Readings
            'propertymanagement.meters.view',
            'propertymanagement.meters.manage',
        ];
    }
}
