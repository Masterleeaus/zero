<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/titanhello/ping', fn () => ['ok' => true])->name('titanhello.api.ping');
