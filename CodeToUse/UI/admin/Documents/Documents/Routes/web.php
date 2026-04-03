<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\DocumentsController;
use Modules\Documents\Http\Controllers\DocumentManagerController;
use Modules\Documents\Http\Controllers\FoldersController;
use Modules\Documents\Http\Controllers\FilesController;
use Modules\Documents\Http\Controllers\DocumentAttachmentsController;
use Modules\Documents\Http\Controllers\AttachmentDownloadController;
use Modules\Documents\Http\Controllers\DocumentSectionsController;
use Modules\Documents\Http\Controllers\DocumentVersionsController;
use Modules\Documents\Http\Controllers\DocumentShareLinksController;
use Modules\Documents\Http\Controllers\DocumentLinksController;
use Modules\Documents\Http\Controllers\DocumentWorkflowController;
use Modules\Documents\Http\Controllers\DocumentRestoreVersionController;
use Modules\Documents\Http\Controllers\DocumentSearchController;
use Modules\Documents\Http\Controllers\DocumentSavedViewsController;
use Modules\Documents\Http\Controllers\TagsController;
use Modules\Documents\Http\Controllers\DocumentTagsController;
use Modules\Documents\Http\Controllers\DocumentOrderingController;
use Modules\Documents\Http\Controllers\DocumentRequestsController;
use Modules\Documents\Http\Controllers\PublicDocumentRequestController;

Route::middleware(['web', 'auth', 'documents.tenant'])
    ->prefix('account/documents')
    ->as('documents.')
    ->group(function () {
        // Core document routes
        Route::get('/', [DocumentsController::class, 'index'])
            ->name('index');

        Route::get('/general', [DocumentsController::class, 'indexGeneral'])
            ->name('general');

        Route::get('/swms', [DocumentsController::class, 'indexSwms'])
            ->name('swms');

        Route::get('/templates', [DocumentsController::class, 'templates'])
            ->name('templates');

        Route::get('/create', [DocumentsController::class, 'create'])
            ->name('create');

        Route::post('/store', [DocumentsController::class, 'store'])
            ->name('store');

        // Template tools
        Route::post('/templates/apply', [DocumentsController::class, 'applyTemplate'])
            ->name('templates.apply');

        // Premium CRUD routes
        Route::get('/{document}', [DocumentsController::class, 'show'])
            ->name('show');

        Route::get('/{document}/edit', [DocumentsController::class, 'edit'])
            ->name('edit');

        Route::put('/{document}', [DocumentsController::class, 'update'])
            ->name('update');

        Route::delete('/{document}', [DocumentsController::class, 'destroy'])
            ->name('destroy');

        Route::get('/templates/{slug}/print', [DocumentsController::class, 'printTemplate'])
            ->name('templates.print');

        // Public view via QR
        Route::get('/public/{id}', [DocumentsController::class, 'publicShow'])
            ->name('public.show');

        // Document Manager dashboard
        Route::get('/manager/dashboard', [DocumentManagerController::class, 'dashboard'])
            ->name('manager.dashboard');

        // Folder & file browser
        Route::resource('folders', FoldersController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('files', FilesController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Attachments tied to documents
        Route::delete('documents/{document}/attachments/{attachment}', [DocumentAttachmentsController::class, 'destroy'])
            ->name('attachments.destroy');

        Route::get('attachments/{attachment}/download', [AttachmentDownloadController::class, 'download'])
            ->name('attachments.download');

        // Structured sections
        Route::post('documents/{document}/sections', [DocumentSectionsController::class, 'store'])
            ->name('sections.store');
        Route::delete('documents/{document}/sections/{section}', [DocumentSectionsController::class, 'destroy'])
            ->name('sections.destroy');

        // Version history
        Route::get('documents/{document}/versions', [DocumentVersionsController::class, 'index'])
            ->name('versions.index');
        Route::get('documents/{document}/versions/{version}', [DocumentVersionsController::class, 'show'])
            ->name('versions.show');

        // Restore version
        Route::post('documents/{document}/versions/{version}/restore', [DocumentRestoreVersionController::class, 'restore'])
            ->name('versions.restore');

        // Workflow (premium)
        Route::post('documents/{document}/workflow/review', [DocumentWorkflowController::class, 'submitForReview'])
            ->name('workflow.review');
        Route::post('documents/{document}/workflow/approve', [DocumentWorkflowController::class, 'approve'])
            ->name('workflow.approve');
        Route::post('documents/{document}/workflow/archive', [DocumentWorkflowController::class, 'archive'])
            ->name('workflow.archive');
        Route::post('documents/{document}/workflow/draft', [DocumentWorkflowController::class, 'revertToDraft'])
            ->name('workflow.draft');

        // Share links (read-only links are implemented in later passes; scaffold now)
        Route::post('documents/{document}/share-links', [DocumentShareLinksController::class, 'store'])
            ->name('share-links.store');
        Route::delete('documents/{document}/share-links/{shareLink}', [DocumentShareLinksController::class, 'destroy'])
            ->name('share-links.destroy');
        Route::post('documents/{document}/share-links/{shareLink}/revoke', [DocumentShareLinksController::class, 'revoke'])
            ->name('share-links.revoke');


        // Entity links (jobs/sites/quotes/invoices; simple polymorphic links)
        Route::post('documents/{document}/links', [DocumentLinksController::class, 'store'])
            ->name('links.store');
        Route::delete('documents/{document}/links/{link}', [DocumentLinksController::class, 'destroy'])
            ->name('links.destroy');
    });


        // Search + saved views
        Route::get('/search', [DocumentSearchController::class, 'index'])->name('documents.search');
        Route::post('documents/views', [DocumentSavedViewsController::class, 'store'])->name('documents.views.store');
        Route::delete('documents/views/{view}', [DocumentSavedViewsController::class, 'destroy'])->name('documents.views.destroy');

        // Sections reorder
        Route::post('documents/sections/reorder', [DocumentSectionsController::class, 'reorder'])->name('documents.sections.reorder');


        // PDF export (premium)
        Route::get('documents/{id}/pdf', [\Modules\Documents\Http\Controllers\DocumentPdfController::class, 'export'])
            ->name('pdf.export');
        Route::get('documents/{id}/pdf/preview', [\Modules\Documents\Http\Controllers\DocumentPdfController::class, 'preview'])
            ->name('pdf.preview');

        // Template governance (admin)
        Route::get('/templates/admin', [\Modules\Documents\Http\Controllers\DocumentTemplateAdminController::class, 'index'])
            ->name('templates.admin.index');
        Route::get('/templates/admin/{id}/edit', [\Modules\Documents\Http\Controllers\DocumentTemplateAdminController::class, 'edit'])
            ->name('templates.admin.edit');
        Route::post('/templates/admin/{id}', [\Modules\Documents\Http\Controllers\DocumentTemplateAdminController::class, 'update'])
            ->name('templates.admin.update');
        Route::post('/templates/admin/{id}/publish', [\Modules\Documents\Http\Controllers\DocumentTemplatePublishController::class, 'publish'])
            ->name('templates.admin.publish');
        Route::post('/templates/admin/{id}/unpublish', [\Modules\Documents\Http\Controllers\DocumentTemplatePublishController::class, 'unpublish'])
            ->name('templates.admin.unpublish');

        // Share links (authenticated management)
        Route::post('documents/{document}/share-links', [\Modules\Documents\Http\Controllers\DocumentShareLinksController::class, 'store'])
        

    // Legacy redirects (older links)
Route::middleware(['web', 'auth', 'documents.tenant'])
    ->prefix('documents')
    ->group(function () {
        Route::get('{any?}', function ($any = null) {
            $path = trim((string) $any, '/');
            $target = 'account/documents' . ($path ? '/' . $path : '');
            return redirect($target);
        })->where('any', '.*');
    });

// Backwards-compatible alias for older sidebar expecting documents.swms.index
Route::middleware(['web', 'auth', 'documents.tenant'])
    ->group(function () {
        if (! Route::has('documents.swms.index')) {
            Route::get('/documents/swms', function () {
                return redirect()->route('documents.swms');
            })->name('documents.swms.index');
        }
    });


// Public share (read-only)
Route::middleware(['web','documents.sharelink'])->get('/documents/share/{token}', [\Modules\Documents\Http\Controllers\DocumentSharePublicController::class, 'show'])->name('documents.share.public');

// Public upload (no auth)
Route::get('/documents/request/{token}', [PublicDocumentRequestController::class, 'show'])->name('documents.request.public');
Route::post('/documents/request/{token}', [PublicDocumentRequestController::class, 'upload'])->name('documents.request.upload');
