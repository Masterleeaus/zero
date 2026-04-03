<?php

return [
    'group' => 'Property Management',
    'permissions' => [
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

        // Settings + AI
        'propertymanagement.settings',
        'propertymanagement.ai',
    ],
];
