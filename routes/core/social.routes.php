<?php

declare(strict_types=1);

use App\Extensions\AISocialMedia\System\Http\Controllers\Api\InstagramController as AiInstagramController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationPlatformController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationSettingController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationStepController;
use App\Extensions\AISocialMedia\System\Http\Controllers\GenerateContentController;
use App\Extensions\AISocialMedia\System\Http\Controllers\UploadController as AiUploadController;
use App\Extensions\AISocialMedia\System\Http\Middleware\AutomationCacheMiddleware;
use App\Extensions\SocialMedia\System\Http\Controllers\Common\DemoDataController;
use App\Extensions\SocialMedia\System\Http\Controllers\Common\SocialMediaCampaignCommonController;
use App\Extensions\SocialMedia\System\Http\Controllers\Common\SocialMediaCompanyCommonController;
use App\Extensions\SocialMedia\System\Http\Controllers\FalAISettingController;
use App\Extensions\SocialMedia\System\Http\Controllers\ImageStatusController;
use App\Extensions\SocialMedia\System\Http\Controllers\Oauth\FacebookController;
use App\Extensions\SocialMedia\System\Http\Controllers\Oauth\InstagramController as SocialInstagramController;
use App\Extensions\SocialMedia\System\Http\Controllers\Oauth\LinkedinController;
use App\Extensions\SocialMedia\System\Http\Controllers\Oauth\TiktokController;
use App\Extensions\SocialMedia\System\Http\Controllers\Oauth\XController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaCalendarController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaCampaignController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaPlatformController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaPostController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaSettingController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaUploadController;
use App\Extensions\SocialMedia\System\Http\Controllers\SocialMediaVideoController;
use App\Extensions\SocialMediaAgent\System\Http\Controllers\SocialMediaAgentAnalysisController;
use App\Extensions\SocialMediaAgent\System\Http\Controllers\SocialMediaAgentChatController;
use App\Extensions\SocialMediaAgent\System\Http\Controllers\SocialMediaAgentChatSettingsController;
use App\Extensions\SocialMediaAgent\System\Http\Controllers\SocialMediaAgentController;
use App\Extensions\SocialMediaAgent\System\Http\Controllers\SocialMediaAgentPostController;
use App\Http\Middleware\CheckTemplateTypeAndPlan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social Suite Routes
|--------------------------------------------------------------------------
| Routes for SocialMedia, AiSocialMedia, and SocialMediaAgent modules.
| Loaded via RouteServiceProvider core loader to avoid duplicate provider wiring.
*/

Route::middleware(['web', 'auth'])->group(function () {
    // SocialMedia OAuth + dashboards
    Route::get('tiktok/verify', [TiktokController::class, 'verify'])->name('tiktok.verify');
    Route::get('social-media-demo-data', DemoDataController::class)->name('demo-data');

    Route::any('social-media/webhook/instagram', [SocialInstagramController::class, 'webhook'])
        ->name('social-media.oauth.webhook.instagram')
        ->withoutMiddleware('auth');
    Route::any('social-media/webhook/facebook', [FacebookController::class, 'webhook'])
        ->name('social-media.oauth.webhook.facebook')
        ->withoutMiddleware('auth');

    Route::prefix('social-media/oauth')->group(function () {
        Route::get('redirect/tiktok', [TiktokController::class, 'redirect'])->name('social-media.oauth.connect.tiktok');
        Route::get('callback/tiktok', [TiktokController::class, 'callback'])->name('social-media.oauth.callback.tiktok');

        Route::get('redirect/instagram', [SocialInstagramController::class, 'redirect'])->name('social-media.oauth.connect.instagram');
        Route::get('callback/instagram', [SocialInstagramController::class, 'callback'])->name('social-media.oauth.callback.instagram');

        Route::get('redirect/x', [XController::class, 'redirect'])->name('social-media.oauth.connect.x');
        Route::get('callback/x', [XController::class, 'callback'])->name('social-media.oauth.callback.x');

        Route::get('redirect/facebook', [FacebookController::class, 'redirect'])->name('social-media.oauth.connect.facebook');
        Route::get('callback/facebook', [FacebookController::class, 'callback'])->name('social-media.oauth.callback.facebook');

        Route::get('redirect/linkedin', [LinkedinController::class, 'redirect'])->name('social-media.oauth.connect.linkedin');
        Route::get('callback/linkedin', [LinkedinController::class, 'callback'])->name('social-media.oauth.callback.linkedin');
    });

    Route::name('dashboard.user.social-media.')
        ->prefix('dashboard/user/social-media')
        ->group(function () {
            Route::get('post', [SocialMediaPostController::class, 'index'])->name('post.index');
            Route::get('post/create', [SocialMediaPostController::class, 'create'])->name('post.create');
            Route::get('post/{post}/edit', [SocialMediaPostController::class, 'edit'])->name('post.edit');
            Route::post('post/{post}/update', [SocialMediaPostController::class, 'update'])->name('post.update');
            Route::get('post/{id}', [SocialMediaPostController::class, 'show'])->name('post.show');
            Route::post('post', [SocialMediaPostController::class, 'store'])->name('post.store');
            Route::post('post/{post}/duplicate', [SocialMediaPostController::class, 'duplicate'])->name('post.duplicate');
            Route::get('post/{post}/delete', [SocialMediaPostController::class, 'destroy'])->name('post.delete');
            Route::post('upload/image', [SocialMediaUploadController::class, 'image'])->name('upload.image');
            Route::post('upload/video', [SocialMediaUploadController::class, 'video'])->name('upload.video');

            Route::get('', SocialMediaController::class)->name('index');
            Route::get('platforms', SocialMediaPlatformController::class)->name('platforms');
            Route::get('platforms/{platform}/disconnect', [SocialMediaPlatformController::class, 'disconnect'])->name('platforms.disconnect');
            Route::post('campaign/generate', [SocialMediaCampaignController::class, 'generate'])->name('campaign.generate');
            Route::any('image/get-status', ImageStatusController::class)->name('image.get.status');

            Route::get('campaign/{campaign}/delete', [SocialMediaCampaignController::class, 'destroy'])->name('campaign.destroy');
            Route::resource('campaign', SocialMediaCampaignController::class)->only('index', 'store');

            Route::get('calendar', SocialMediaCalendarController::class)->name('calendar');

            Route::post('video/generate', SocialMediaVideoController::class)->name('video.generate');
            Route::get('video/status', [SocialMediaVideoController::class, 'status'])->name('video.status');
        });

    Route::name('dashboard.user.social-media.common.')
        ->prefix('dashboard/user/social-media/common')
        ->group(function () {
            Route::get('companies', SocialMediaCompanyCommonController::class)->name('companies');
            Route::post('campaigns', SocialMediaCampaignCommonController::class)->name('campaigns');
            Route::get('generate-content', [SocialMediaCampaignCommonController::class, 'generate'])->name('campaigns.generate.content');
        });

    Route::prefix('dashboard/user/automation/campaign')->group(function () {
        Route::post('genContent', [SocialMediaCampaignCommonController::class, 'generate'])
            ->name('dashboard.user.automation.campaign.genContent');
    });

    Route::middleware('admin')
        ->prefix('dashboard/admin/social-media/setting')
        ->name('dashboard.admin.social-media.setting.')
        ->controller(SocialMediaSettingController::class)
        ->group(function () {
            Route::get('', 'index')->name('index');
            Route::post('{platform}/update', 'update')->name('update');
        });

    Route::controller(FalAISettingController::class)
        ->prefix('dashboard/admin/settings')
        ->middleware(['auth', 'admin'])
        ->name('dashboard.admin.settings.')
        ->group(function () {
            Route::get('fal-ai', 'index')->name('fal-ai');
            Route::post('fal-ai', 'update')->name('fal-ai.update');
        });

    // AiSocialMedia OAuth + automation
    Route::group([
        'prefix'     => 'oauth',
        'controller' => AiInstagramController::class,
    ], function () {
        Route::get('redirect/instagram', 'redirect')->name('oauth.connect.instagram');
        Route::get('callback/instagram', 'callback')->name('oauth.callback.instagram');
    });

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::prefix('user')
            ->name('user.')
            ->group(function () {
                Route::controller(AutomationPlatformController::class)
                    ->prefix('automation/platform')
                    ->name('automation.platform.')
                    ->group(function () {
                        Route::get('', 'index')->name('list');
                        Route::post('update/{platform}', 'update')->name('update');
                        Route::get('disconnect/{automationPlatform}', 'disconnect')->name('disconnect');
                    });

                Route::controller(AutomationController::class)
                    ->prefix('automation')
                    ->name('automation.')
                    ->group(function () {
                        Route::post('upload', AiUploadController::class)->name('upload');
                        Route::post('genPost', [GenerateContentController::class, 'generateContent'])->name('generate-content');

                        Route::group([
                            'controller' => AutomationStepController::class,
                            'middleware' => AutomationCacheMiddleware::class,
                        ], static function () {
                            Route::get('', 'stepFirst')->name('index')->middleware(CheckTemplateTypeAndPlan::class)->withoutMiddleware(AutomationCacheMiddleware::class);
                            Route::any('step/two', 'stepSecond')->name('step.second');
                            Route::any('step/third', 'stepThird')->name('step.third');
                            Route::any('step/fourth', 'stepFourth')->name('step.fourth');
                            Route::any('step/fifth', 'stepFifth')->name('step.fifth');
                            Route::any('step/last', 'stepLast')->name('step.last');
                            Route::any('step/store', 'storeScheduledPost')->name('step.store');
                        });

                        Route::post('', 'nextStep')->name('postindex');

                        Route::get('scheduled-posts', 'scheduledPosts')->name('list')->middleware(CheckTemplateTypeAndPlan::class);
                        Route::get('scheduled-posts/delete/{id}', 'scheduledPostsDelete')->name('delete');
                        Route::post('scheduled-posts/edit/{id}', 'scheduledPostsEdit')->name('edit');

                        Route::prefix('platform')
                            ->name('platform.')
                            ->group(function () {
                            });

                        Route::prefix('company')
                            ->name('company.')
                            ->group(function () {
                                Route::get('get-products/{company_id}', 'getProducts')->name('getProducts');
                            });

                        Route::prefix('campaign')
                            ->name('campaign.')
                            ->group(function () {
                                Route::get('', 'campaignList')->name('list');
                                Route::get('add-or-update/{id?}', 'campaignAddOrUpdate')->name('addOrUpdate');
                                Route::get('delete/{id?}', 'campaignDelete')->name('delete');
                                Route::post('save', 'campaignAddOrUpdateSave')->name('campaignAddOrUpdateSave');
                                Route::get('get-target/{campaign_id}', 'getCampaignTarget')->name('getCampaignTarget');
                                // Route::post('genContent', 'generateCampaignContent')->name('genContent');
                                Route::post('genTopics', 'generateCampaignTopics')->name('genTopics');
                            });

                        Route::post('update', 'updateAutomation')->name('update');
                        Route::post('getCompany', 'getCompany')->name('getCompany');
                        Route::post('getSelectedProducts', 'getSelectedProducts')->name('getSelectedProducts');
                    });
            });

        Route::prefix('admin')
            ->middleware('admin')
            ->name('admin.')
            ->group(function () {
                Route::controller(AutomationSettingController::class)
                    ->prefix('automation')
                    ->name('automation.')
                    ->group(function () {
                        Route::get('settings', 'index')->name('settings');
                        Route::post('settings/update', 'update')->name('settings.update');
                    });
            });
    });

    // SocialMediaAgent dashboards
    Route::name('dashboard.user.social-media.agent.')
        ->prefix('dashboard/user/social-media/agent')
        ->group(function () {
            Route::get('chat/{id?}', [SocialMediaAgentChatController::class, 'index'])->name('chat.index');
            Route::get('', [SocialMediaAgentController::class, 'index'])->name('index');
            Route::get('post-items', [SocialMediaAgentController::class, 'postItems'])->name('post-items');
            Route::get('create', [SocialMediaAgentController::class, 'create'])->name('create');
            Route::get('agents', [SocialMediaAgentController::class, 'agents'])->name('agents');
            Route::get('calendar', [SocialMediaAgentController::class, 'calendar'])->name('calendar');
            Route::get('posts', [SocialMediaAgentController::class, 'posts'])->name('posts');
            Route::get('analytics', [SocialMediaAgentController::class, 'analytics'])->name('analytics');
            Route::get('accounts', [SocialMediaAgentController::class, 'accounts'])->name('accounts');
            Route::post('', [SocialMediaAgentController::class, 'store'])->name('store');
            Route::get('{agent}/edit', [SocialMediaAgentController::class, 'edit'])->name('edit');
            Route::put('{agent}', [SocialMediaAgentController::class, 'update'])->name('update');
            Route::delete('{agent}', [SocialMediaAgentController::class, 'destroy'])->name('destroy');

            Route::post('scrape-website', [SocialMediaAgentController::class, 'scrapeWebsite'])->name('scrape-website');
            Route::post('generate-targets', [SocialMediaAgentController::class, 'generateTargets'])->name('generate-targets');
            Route::post('preview-post', [SocialMediaAgentController::class, 'previewPost'])->name('preview-post');

            Route::post('{agent}/generate-posts', [SocialMediaAgentController::class, 'generatePosts'])->name('generate-posts');
            Route::post('posts/{post}/approve', [SocialMediaAgentController::class, 'approvePost'])->name('posts.approve');
            Route::post('{agent}/approve-bulk', [SocialMediaAgentController::class, 'approveBulk'])->name('approve-bulk');
            Route::delete('posts/{post}/reject', [SocialMediaAgentController::class, 'rejectPost'])->name('posts.reject');
            Route::post('posts/{post}/duplicate', [SocialMediaAgentController::class, 'duplicatePost'])->name('posts.duplicate');

            Route::get('analyses', [SocialMediaAgentAnalysisController::class, 'index'])->name('analyses.index');
            Route::get('analyses/{analysis}', [SocialMediaAgentAnalysisController::class, 'show'])->name('analyses.show');
            Route::post('analyses/{analysis}/read', [SocialMediaAgentAnalysisController::class, 'markAsRead'])->name('analyses.mark-read');
            Route::delete('analyses/{analysis}', [SocialMediaAgentAnalysisController::class, 'destroy'])->name('analyses.destroy');
            Route::delete('analyses', [SocialMediaAgentAnalysisController::class, 'clearAll'])->name('analyses.clear-all');

            Route::get('api/pending-count', [SocialMediaAgentController::class, 'getPendingCount'])->name('api.pending-count');
            Route::get('api/posts', [SocialMediaAgentController::class, 'getPosts'])->name('api.posts');
            Route::post('api/posts', [SocialMediaAgentController::class, 'storePost'])->name('api.posts.store');
            Route::post('api/upload-image', [SocialMediaAgentController::class, 'uploadImage'])->name('api.upload-image');
            Route::put('api/posts/{post}', [SocialMediaAgentController::class, 'updatePost'])->name('api.posts.update');
            Route::post('api/posts/generate-content', [SocialMediaAgentController::class, 'generatePostContent'])->name('api.posts.generate-content');
            Route::post('api/posts/{post}/regenerate', [SocialMediaAgentPostController::class, 'regenerateContent'])->name('api.posts.regenerate');
            Route::post('api/posts/generate-image', [SocialMediaAgentController::class, 'generatePostImage'])->name('api.posts.generate-image');
            Route::get('api/generation-status', [SocialMediaAgentController::class, 'getGenerationStatus'])->name('api.generation-status');
        });

    Route::prefix('dashboard/admin/social-media/agent/chat')
        ->name('dashboard.admin.social-media.agent.chat.')
        ->middleware('admin')
        ->group(function () {
            Route::get('settings', [SocialMediaAgentChatSettingsController::class, 'index'])->name('settings');
            Route::post('settings', [SocialMediaAgentChatSettingsController::class, 'update'])->name('settings.update');
        });
});

Route::middleware('api')->group(function () {
    Route::post('social-media-agent/fal-webhook', [SocialMediaAgentController::class, 'falWebhook'])->name('dashboard.user.social-media.agent.fal-webhook');
});
