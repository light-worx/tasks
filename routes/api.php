<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskMetaController;
use App\Http\Controllers\Api\TaskQueryController;
use App\Http\Controllers\ClientAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/clients/register', [ClientAuthController::class, 'register']);
Route::post('/clients/token', [ClientAuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::get('tasks/assignee/{email}', [TaskQueryController::class, 'byAssignee']);
    Route::get('/tasks/meta', [TaskMetaController::class, 'index']);
});
