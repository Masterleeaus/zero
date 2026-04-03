<?php

return [
        ['route_file' => 'api', 'method' => 'GET', 'endpoint' => '/ping'],
        ['route_file' => 'api', 'method' => 'GET', 'endpoint' => '/settings'],
        ['route_file' => 'api', 'method' => 'POST', 'endpoint' => '/settings'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/'],
        ['route_file' => 'web', 'method' => 'POST', 'endpoint' => '/'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/create'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/diagnostics'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/runs'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/runs/{runId}'],
        ['route_file' => 'web', 'method' => 'PUT', 'endpoint' => '/{id}'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/{id}/edit'],
        ['route_file' => 'web', 'method' => 'POST', 'endpoint' => '/{id}/run'],
        ['route_file' => 'web', 'method' => 'GET', 'endpoint' => '/{id}/timeline'],
];
