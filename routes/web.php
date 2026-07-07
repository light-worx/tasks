<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pwa\AppController;
use App\Http\Controllers\Pwa\ProjectController;
use App\Http\Controllers\Pwa\TaskController;
use App\Http\Controllers\Pwa\EmailVerificationController;

Route::domain('app.' . parse_url(config('app.url'), PHP_URL_HOST))
    ->middleware(['web', 'pwa.device'])
    ->group(function () {

        Route::view('/signin', 'pwa-app.signin')->name('app.signin');
        Route::post('/signin/send-pin',    [EmailVerificationController::class, 'sendPin']);
        Route::post('/signin/confirm-pin', [EmailVerificationController::class, 'verifyPin']);

        Route::middleware('pwa.identity')->group(function () {
            Route::get('/',                    [AppController::class, 'home'])->name('app.home');
            Route::get('/projects',            [ProjectController::class, 'index'])->name('app.projects');
            Route::get('/projects/create',      [ProjectController::class, 'create'])->name('app.projects.create');
            Route::post('/projects',            [ProjectController::class, 'store'])->name('app.projects.store');
            Route::get('/projects/{project}',   [ProjectController::class, 'show'])->name('app.projects.show');
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('app.projects.edit');
            Route::put('/projects/{project}',   [ProjectController::class, 'update'])->name('app.projects.update');
            Route::delete('/projects/{project}',[ProjectController::class, 'destroy'])->name('app.projects.destroy');
            Route::get('/tasks',               [TaskController::class, 'index'])->name('app.tasks');
            Route::get('/tasks/create',        [TaskController::class, 'create'])->name('app.tasks.create');
            Route::post('/tasks',              [TaskController::class, 'store'])->name('app.tasks.store');
            Route::get('/tasks/{task}',         [TaskController::class, 'show'])->name('app.tasks.show');
            Route::get('/tasks/{task}/edit',    [TaskController::class, 'edit'])->name('app.tasks.edit');
            Route::put('/tasks/{task}',         [TaskController::class, 'update'])->name('app.tasks.update');
            Route::patch('/tasks/{task}/status',[TaskController::class, 'updateStatus'])->name('app.tasks.status');
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('app.tasks.destroy');
        });
    });