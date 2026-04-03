<?php

use App\Titan\Signals\SignalsService;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('dashboard/user/titan-signals')->group(function () {
    Route::get('demo', function () {
        $companyId = (int) (auth()->user()->company_id ?? 0);
        $teamId = auth()->user()->team_id ? (int) auth()->user()->team_id : null;

        return response()->json(app(SignalsService::class)->all($companyId, $teamId, auth()->id()));
    })->name('dashboard.user.titan-signals.index');

    Route::get('envelope', function () {
        $companyId = (int) (auth()->user()->company_id ?? 0);
        $teamId = auth()->user()->team_id ? (int) auth()->user()->team_id : null;

        return response()->json(app(SignalsService::class)->envelope($companyId, $teamId, auth()->id()));
    })->name('dashboard.user.titan-signals.envelope');
});
