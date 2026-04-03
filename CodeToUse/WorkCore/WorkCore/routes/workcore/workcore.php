<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkCore Routes
|--------------------------------------------------------------------------
|
| This file is intended to be auto-loaded by the MagicAI host application's
| routes/web.php loader. Devs can add more files into routes/workcore/ such as:
| customers.php, sites.php, service_jobs.php, checklists.php, team.php, money.php
|
*/

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/user')
    ->name('dashboard.user.')
    ->group(function () {
        // WorkCore domain routes go here.
    });
