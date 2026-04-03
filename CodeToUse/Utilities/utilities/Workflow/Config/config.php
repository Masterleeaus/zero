<?php
return [
    'name'       => 'Workflow',
    // keep these present but empty; avoids "undefined index" later
    'aliases'    => [],
    'providers'  => [],
    'permissions'=> [],
    'settings'   => [],
    'endpoints'  => require __DIR__ . '/endpoints.php',
];
