<?php
use Illuminate\Support\Facades\Route;

// Channel Admin
Route::middleware(['web','auth'])->prefix('aiconverse')->group(function () {
  Route::get('/channels', 'ChannelController@index')->name('titantalk.channels.index');
  Route::get('/channels/create', 'ChannelController@create')->name('titantalk.channels.create');
  Route::post('/channels', 'ChannelController@store')->name('titantalk.channels.store');
  Route::get('/channels/{id}/edit', 'ChannelController@edit')->name('titantalk.channels.edit');
  Route::put('/channels/{id}', 'ChannelController@update')->name('titantalk.channels.update');
  Route::delete('/channels/{id}', 'ChannelController@destroy')->name('titantalk.channels.delete');
});

// Webhooks (generic): /api/titantalk/hook/{driver}
Route::middleware(['api'])->prefix('api/aiconverse')->group(function () {
  Route::post('/hook/{driver}', 'WebhookController@receive')->name('titantalk.webhook.receive');
});
