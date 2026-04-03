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

Route::middleware(['web','auth'])->prefix('account')->group(function () {
Route::middleware(['web', 'auth'])
    ->prefix('documents')
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

        // Share links (read-only links are implemented in later passes; scaffold now)
        Route::post('documents/{document}/share-links', [DocumentShareLinksController::class, 'store'])
            ->name('share-links.store');
        Route::delete('documents/{document}/share-links/{shareLink}', [DocumentShareLinksController::class, 'destroy'])
            ->name('share-links.destroy');

        // Entity links (jobs/sites/quotes/invoices later; scaffold now)
        Route::post('documents/{document}/links', [DocumentLinksController::class, 'store'])
            ->name('links.store');
        Route::delete('documents/{document}/links/{link}', [DocumentLinksController::class, 'destroy'])
            ->name('links.destroy');
    });

// Backwards-compatible alias for older sidebar expecting documents.swms.index
Route::middleware(['web', 'auth'])
    ->group(function () {
        if (! Route::has('documents.swms.index')) {
            Route::get('/documents/swms', function () {
                return redirect()->route('documents.swms');
            })->name('documents.swms.index');
        }
    });
});
