<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanHello\Http\Controllers\DashboardController;
use Modules\TitanHello\Http\Controllers\SettingsController;
use Modules\TitanHello\Http\Controllers\Calls\CallInboxController;
use Modules\TitanHello\Http\Controllers\Calls\CallActionsController;
use Modules\TitanHello\Http\Controllers\Calls\DialerController;
use Modules\TitanHello\Http\Controllers\Webhooks\CallWebhookController;
use Modules\TitanHello\Http\Controllers\Routing\InboundNumberController;
use Modules\TitanHello\Http\Controllers\Routing\RingGroupController;
use Modules\TitanHello\Http\Controllers\Routing\IvrController;
use Modules\TitanHello\Http\Controllers\Campaigns\DialCampaignController;
use Modules\TitanHello\Http\Controllers\Callbacks\CallbackInboxController;
use Modules\TitanHello\Http\Controllers\Callbacks\CallbackActionsController;

Route::middleware(['web', 'auth'])
    ->prefix('account/titanhello')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware(['titanhello.tenant'])
            ->name('titanhello.dashboard');

        Route::get('/health', [\Modules\TitanHello\Http\Controllers\HealthController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.admin.view'])
            ->name('titanhello.health');

        Route::get('/settings', [SettingsController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.settings.manage'])
            ->name('titanhello.settings.index');

        Route::post('/settings', [SettingsController::class, 'save'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.settings.manage'])
            ->name('titanhello.settings.save');

        Route::get('/calls', [CallInboxController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.view'])
            ->name('titanhello.calls.index');

        Route::get('/calls/{id}', [CallInboxController::class, 'show'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.view'])
            ->name('titanhello.calls.show');


        Route::post('/calls/{id}/assign', [CallActionsController::class, 'assign'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.assign'])
            ->name('titanhello.calls.assign');

        Route::post('/calls/{id}/disposition', [CallActionsController::class, 'setDisposition'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.update'])
            ->name('titanhello.calls.disposition');

        Route::post('/calls/{id}/callback', [CallActionsController::class, 'setCallback'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.update'])
            ->name('titanhello.calls.callback');

        Route::post('/calls/{id}/notes', [CallActionsController::class, 'addNote'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.update'])
            ->name('titanhello.calls.notes');

        // Callbacks
        Route::get('/callbacks', [CallbackInboxController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.view'])
            ->name('titanhello.callbacks.index');

        Route::get('/callbacks/{id}', [CallbackInboxController::class, 'show'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.view'])
            ->name('titanhello.callbacks.show');

        Route::post('/callbacks/{id}/assign', [CallbackActionsController::class, 'assign'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.update'])
            ->name('titanhello.callbacks.assign');

        Route::post('/callbacks/{id}/due', [CallbackActionsController::class, 'setDue'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.update'])
            ->name('titanhello.callbacks.due');

        Route::post('/callbacks/{id}/done', [CallbackActionsController::class, 'done'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.update'])
            ->name('titanhello.callbacks.done');

        Route::post('/callbacks/{id}/cancel', [CallbackActionsController::class, 'cancel'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.callbacks.update'])
            ->name('titanhello.callbacks.cancel');

        Route::get('/dialer', [DialerController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.callout'])
            ->name('titanhello.calls.dialer');


        // Routing
        Route::get('/routing/numbers', [InboundNumberController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.view'])
            ->name('titanhello.routing.numbers.index');

        Route::get('/routing/numbers/create', [InboundNumberController::class, 'create'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.numbers.create');

        Route::post('/routing/numbers', [InboundNumberController::class, 'store'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.numbers.store');

        Route::get('/routing/numbers/{id}/edit', [InboundNumberController::class, 'edit'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.numbers.edit');

        Route::post('/routing/numbers/{id}', [InboundNumberController::class, 'update'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.numbers.update');

        Route::post('/routing/numbers/{id}/delete', [InboundNumberController::class, 'destroy'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.numbers.delete');

        Route::get('/routing/ring-groups', [RingGroupController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.view'])
            ->name('titanhello.routing.ringgroups.index');

        Route::get('/routing/ring-groups/create', [RingGroupController::class, 'create'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.create');

        Route::post('/routing/ring-groups', [RingGroupController::class, 'store'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.store');

        Route::get('/routing/ring-groups/{id}/edit', [RingGroupController::class, 'edit'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.edit');

        Route::post('/routing/ring-groups/{id}', [RingGroupController::class, 'update'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.update');

        Route::post('/routing/ring-groups/{id}/members', [RingGroupController::class, 'addMember'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.members.add');

        Route::post('/routing/ring-groups/{id}/members/{memberId}/delete', [RingGroupController::class, 'deleteMember'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.members.delete');

        Route::post('/routing/ring-groups/{id}/delete', [RingGroupController::class, 'destroy'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ringgroups.delete');

        Route::get('/routing/ivr', [IvrController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.view'])
            ->name('titanhello.routing.ivr.index');

        Route::get('/routing/ivr/create', [IvrController::class, 'create'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.create');

        Route::post('/routing/ivr', [IvrController::class, 'store'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.store');

        Route::get('/routing/ivr/{id}/edit', [IvrController::class, 'edit'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.edit');

        Route::post('/routing/ivr/{id}', [IvrController::class, 'update'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.update');

        Route::post('/routing/ivr/{id}/options', [IvrController::class, 'addOption'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.options.add');

        Route::post('/routing/ivr/{id}/options/{optId}/delete', [IvrController::class, 'deleteOption'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.options.delete');

        Route::post('/routing/ivr/{id}/delete', [IvrController::class, 'destroy'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.routing.manage'])
            ->name('titanhello.routing.ivr.delete');

        // Dial campaigns
        Route::get('/campaigns', [DialCampaignController::class, 'index'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.view'])
            ->name('titanhello.campaigns.index');

        Route::get('/campaigns/create', [DialCampaignController::class, 'create'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.create');

        Route::post('/campaigns', [DialCampaignController::class, 'store'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.store');

        Route::get('/campaigns/{id}/edit', [DialCampaignController::class, 'edit'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.edit');

        Route::post('/campaigns/{id}', [DialCampaignController::class, 'update'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.update');

        Route::post('/campaigns/{id}/contacts', [DialCampaignController::class, 'addContact'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.contacts.add');

        Route::post('/campaigns/{id}/run', [DialCampaignController::class, 'run'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.run');

        Route::post('/campaigns/{id}/delete', [DialCampaignController::class, 'destroy'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.campaigns.manage'])
            ->name('titanhello.campaigns.delete');


        Route::post('/dialer/call', [DialerController::class, 'call'])
            ->middleware(['titanhello.tenant', 'titanhello.perm:titanhello.calls.callout'])
            ->name('titanhello.calls.dialer.call');

    });

Route::middleware(['web'])
    ->prefix('titanhello/webhooks/voice')
    ->group(function () {
        Route::post('/inbound', [CallWebhookController::class, 'inbound'])->name('titanhello.webhook.inbound');
        Route::post('/recording', [CallWebhookController::class, 'recording'])->name('titanhello.webhook.recording');
        Route::post('/status', [CallWebhookController::class, 'status'])->name('titanhello.webhook.status');
        Route::post('/twiml/outbound', [CallWebhookController::class, 'outboundTwiml'])->name('titanhello.twiml.outbound');
    });


// Webhooks (no auth) - configure provider to call these URLs
Route::post('/titanhello/webhooks/voice/inbound', [CallWebhookController::class, 'inbound'])
    ->name('titanhello.webhooks.voice.inbound');

Route::post('/titanhello/webhooks/voice/ivr/{menuId}/select', [CallWebhookController::class, 'ivrSelect'])
    ->name('titanhello.webhooks.voice.ivr.select');

Route::post('/titanhello/webhooks/voice/outbound-twiml', [CallWebhookController::class, 'outboundTwiml'])
    ->name('titanhello.webhooks.voice.outbound_twiml');

Route::post('/titanhello/webhooks/voice/status', [CallWebhookController::class, 'status'])
    ->name('titanhello.webhooks.voice.status');

Route::post('/titanhello/webhooks/voice/recording', [CallWebhookController::class, 'recording'])
    ->name('titanhello.webhooks.voice.recording');
