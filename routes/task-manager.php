<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskManagerController;

Route::get('/tasks', [TaskManagerController::class, 'index']);
Route::get('/tasks/board', [TaskManagerController::class, 'board']);
Route::get('/tasks/calendar', [TaskManagerController::class, 'calendar']);
Route::get('/tasks/gantt', [TaskManagerController::class, 'gantt']);
