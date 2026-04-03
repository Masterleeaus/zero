<?php

use App\Titan\Signals\SignalsService;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard/user/titan-signals')
    ->as('dashboard.user.titan-signals.')
    ->group(static function () {
        Route::get('demo', static function () {
            $companyId = (int) (auth()->user()->company_id ?? 0);
            $teamId = auth()->user()->team_id ? (int) auth()->user()->team_id : null;

            return response()->json(app(SignalsService::class)->all($companyId, $teamId, auth()->id()));
        })->name('index');

        Route::get('envelope', static function () {
            $companyId = (int) (auth()->user()->company_id ?? 0);
            $teamId = auth()->user()->team_id ? (int) auth()->user()->team_id : null;

            return response()->json(app(SignalsService::class)->envelope($companyId, $teamId, auth()->id()));
        })->name('envelope');
    });
