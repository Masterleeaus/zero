<?php

use Illuminate\Support\Facades\Route;
use Modules\Recruitment\Http\Controllers\CustomQuestionController;
use Modules\Recruitment\Http\Controllers\InterviewScheduleController;
use Modules\Recruitment\Http\Controllers\JobApplicationController;
use Modules\Recruitment\Http\Controllers\JobCategoryController;
use Modules\Recruitment\Http\Controllers\JobController;
use Modules\Recruitment\Http\Controllers\JobStageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//------------------------------------  Recurtment --------------------------------


Route::group(['middleware' => 'PlanModuleCheck:Recruitment'], function ()
 {
    Route::resource('job-category', JobCategoryController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-stage', JobStageController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-stage/order', [JobStageController::class, 'order'])->name('job.stage.order')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job', JobController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-grid', [JobController::class, 'grid'])->name('job.grid')->middleware(
        [
            'auth'
        ]
    );



    Route::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name('job.application.candidate')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-application', JobApplicationController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-application-list', [JobApplicationController::class, 'list'])->name('job.list')->middleware(
        [
            'auth'
        ]
    );

    Route::post('job-application/order', [JobApplicationController::class, 'order'])->name('job.application.order')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name('job.application.rating')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name('job.application.archive')->middleware(
        [
            'auth',
        ]
    );

    Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name('job.application.skill.store')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name('job.application.note.store')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name('job.application.note.destroy')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard-grid', [JobApplicationController::class, 'grid'])->name('job.on.board.grid')->middleware(
        [
            'auth'
        ]
    );

    Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name('job.on.board')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name('job.on.board.create')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name('job.on.board.store')->middleware(
        [
            'auth',
        ]
    );

    Route::get('job-onboard/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name('job.on.board.edit')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name('job.on.board.update')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-onboard/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name('job.on.board.delete')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name('job.on.board.converts')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name('job.on.board.convert')->middleware(
        [
            'auth',
        ]
    );


    Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name('job.application.stage.change')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('custom-question', CustomQuestionController::class)->middleware(
        [
            'auth',
        ]
    );


    Route::resource('interview-schedule', InterviewScheduleController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('interview-schedule/create/{id?}', [InterviewScheduleController::class, 'create'])->name('interview-schedule.create')->middleware(
        [
            'auth',
        ]
    );
    //offer Letter
    Route::post('setting/offerlatter/{lang?}', [JobApplicationController::class, 'offerletterupdate'])->name('offerlatter.update');
    Route::get('job-onboard/pdf/{id}', [JobApplicationController::class, 'offerletterPdf'])->name('offerlatter.download.pdf');
    Route::get('job-onboard/doc/{id}', [JobApplicationController::class, 'offerletterDoc'])->name('offerlatter.download.doc');
    });

    Route::get('career/{id?}/{lang?}', [JobController::class, 'career'])->name('career');

    Route::get('job/requirement/{code}/{lang}', [JobController::class, 'jobRequirement'])->name('job.requirement');
    Route::get('job/apply/{code}/{lang}', [JobController::class, 'jobApply'])->name('job.apply');
    Route::post('job/apply/data/{code}', [JobController::class, 'jobApplyData'])->name('job.apply.data');

//------------------------------------ End Recurtment --------------------------------
