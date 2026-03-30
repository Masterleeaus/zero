<?php

return [
    'group' => 'Managed Premises',
    'permissions' => [
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

        // Settings + AI
        'managedpremises.settings',
        'managedpremises.ai',
    ],
];
