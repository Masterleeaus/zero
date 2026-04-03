<?php
use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\WebhookController;

// Inbound webhooks (no auth; protect with secrets at your edge if desired)
Route::post('/titantalk/hook/{channel}', [WebhookController::class, 'receive']);

// Optional: Web test endpoint (auth)
Route::middleware(['auth'])->get('/titantalk/widget', function(){ return view('titantalk::partials.widget'); })->name('titantalk.widget');
