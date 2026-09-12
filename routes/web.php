<?php

use App\Http\Controllers\TaskManagerController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/', function (Request $request) {

    if ($request->has('code') || $request->has('error')) {
        return app(SettingsController::class)
            ->clickupCallback($request);
    }

	return app(TaskManagerController::class)
		->dashboard($request);
		})->name('dashboard');


	Route::get('/dashboard', [
		TaskManagerController::class,
		'dashboard'
		])->name('dashboard.index');

	Route::get('/agenda', [TaskManagerController::class, 'agenda'])
		->name('agenda');

	Route::get('/inbox', [TaskManagerController::class, 'inbox'])
		->name('inbox');

	Route::get('/favorites', [TaskManagerController::class, 'favorites'])
		->name('favorites');

	Route::get('/members', [MemberController::class, 'index'])
		->name('members.index');

	Route::post('/members', [MemberController::class, 'store'])
		->name('members.store');

	Route::delete('/members/{member}', [MemberController::class, 'destroy'])
		->name('members.destroy');

	Route::get('/members/search-users', [MemberController::class, 'searchUsers'])
		->name('members.search-users');

	Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings');

    Route::get('/settings/clickup', [SettingsController::class, 'clickup'])
        ->name('settings.clickup');

	Route::get('/settings/clickup/connect', [SettingsController::class, 'clickupConnect'])
		->name('settings.clickup.connect');

	Route::get('/settings/clickup/callback', [SettingsController::class, 'clickupCallback'])
		->name('settings.clickup.callback');

    Route::get('/tasks', [TaskManagerController::class, 'index'])
        ->name('tasks.index');

    Route::post('/tasks', [TaskManagerController::class, 'store'])
        ->name('tasks.store');

    Route::put('/tasks/{task}', [TaskManagerController::class, 'update'])
        ->name('tasks.update');

    // ASSIGN / UNASSIGN PIC
    Route::patch('/tasks/{task}/assignee', [TaskManagerController::class, 'updateAssignee'])
        ->name('tasks.assignee.update');

    Route::delete('/tasks/{task}', [TaskManagerController::class, 'destroy'])
        ->name('tasks.destroy');

    Route::patch('/tasks/{task}/status', [TaskManagerController::class, 'updateStatus'])
        ->name('tasks.status');

    Route::patch('/tasks/reorder', [TaskManagerController::class, 'reorder'])
        ->name('tasks.reorder');

    Route::get('/tasks/board', [TaskManagerController::class, 'board'])
        ->name('tasks.board');

    Route::get('/tasks/calendar', [TaskManagerController::class, 'calendar'])
        ->name('tasks.calendar');

    Route::get('/tasks/gantt', [TaskManagerController::class, 'gantt'])
        ->name('tasks.gantt');

	Route::get('/tasks/gantt/previous',[TaskManagerController::class, 'ganttPrevious'])
		->name('tasks.gantt.previous');

	Route::get('/tasks/gantt/next',[TaskManagerController::class, 'ganttNext'])
		->name('tasks.gantt.next');

	Route::post('/tasks/{task}/dependency',[TaskManagerController::class, 'storeDependency'])
		->name('tasks.dependency.store');

	Route::delete('/tasks/{task}/dependency/{dependency}',[TaskManagerController::class, 'destroyDependency'])
		->name('tasks.dependency.destroy');


    // =========================================================
    // SUB TASK
    // =========================================================

    Route::post('/tasks/{task}/subtasks', [TaskManagerController::class, 'storeSubtask'])
        ->name('tasks.subtasks.store');

    Route::put('/subtasks/{task}', [TaskManagerController::class, 'updateSubtask'])
        ->name('tasks.subtasks.update');

    Route::delete('/subtasks/{task}', [TaskManagerController::class, 'destroySubtask'])
        ->name('tasks.subtasks.destroy');


    // =========================================================
    // WORKSPACE
    // =========================================================

    Route::get('/workspaces', [WorkspaceController::class, 'index'])
        ->name('workspaces.index');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])
        ->name('workspaces.store');

    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update'])
        ->name('workspaces.update');

    Route::patch('/workspaces/{workspace}/toggle', [WorkspaceController::class, 'toggle'])
        ->name('workspaces.toggle');

    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])
        ->name('workspaces.destroy');

    Route::post('/workspace/switch/{workspace}', [WorkspaceController::class, 'switch'])
        ->name('workspace.switch');

    Route::post('/workspace/clear', [WorkspaceController::class, 'clear'])
        ->name('workspace.clear');

    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])
        ->name('workspaces.show');


	// SPACE
    Route::get('/spaces', [SpaceController::class, 'index'])
        ->name('spaces.index');
    Route::post('/spaces', [SpaceController::class, 'store'])
        ->name('spaces.store');
    Route::get('/spaces/{space}', [SpaceController::class, 'show'])
        ->name('spaces.show');
    Route::put('/spaces/{space}', [SpaceController::class, 'update'])
        ->name('spaces.update');
    Route::patch('/spaces/{space}/toggle', [SpaceController::class, 'toggle'])
        ->name('spaces.toggle');
    Route::delete('/spaces/{space}', [SpaceController::class, 'destroy'])
        ->name('spaces.destroy');

	// =========================================================
	// FOLDER
	// =========================================================

	Route::get(
		'/workspaces/{workspace}/spaces/{space}/folders',
		[FolderController::class, 'index']
	)->name('folders.index');

	Route::post(
		'/workspaces/{workspace}/spaces/{space}/folders',
		[FolderController::class, 'store']
	)->name('folders.store');

	Route::put(
		'/workspaces/{workspace}/spaces/{space}/folders/{folder}',
		[FolderController::class, 'update']
	)->name('folders.update');

	Route::patch(
		'/workspaces/{workspace}/spaces/{space}/folders/{folder}/toggle',
		[FolderController::class, 'toggle']
	)->name('folders.toggle');

	Route::delete(
		'/workspaces/{workspace}/spaces/{space}/folders/{folder}',
		[FolderController::class, 'destroy']
	)->name('folders.destroy');


	// =========================================================
	// TASK LIST
	// =========================================================

	// TASK LIST
	Route::get('/spaces/{space}/lists',
		[TaskListController::class, 'index']
	)->name('task-lists.index');

	Route::post('/spaces/{space}/lists',
		[TaskListController::class, 'store']
	)->name('task-lists.store');

	Route::put('/spaces/{space}/lists/{taskList:slug}',
		[TaskListController::class, 'update']
	)->name('task-lists.update');

	Route::patch('/spaces/{space}/lists/{taskList:slug}/toggle',
		[TaskListController::class, 'toggle']
	)->name('task-lists.toggle');

	Route::delete('/spaces/{space}/lists/{taskList:slug}',
		[TaskListController::class, 'destroy']
	)->name('task-lists.destroy');


	// LIST PAGE
	Route::get('/spaces/{space}/{taskList}',
		[TaskManagerController::class, 'listPage']
	)->name('tasks.list-page');


	// TASK DETAIL
	Route::get('/tasks/{task}', [TaskManagerController::class, 'show'])
		->name('tasks.show');

    // =========================================================
    // TASK DETAIL
    // HARUS PALING BAWAH SETELAH ROUTE STATIS
    // =========================================================

    Route::get('/tasks/{task}', [TaskManagerController::class, 'show'])
        ->name('tasks.show');


    // =========================================================
    // MANAGE - ACTIVITY
    // =========================================================

    Route::get('/manage/activity', [ActivityController::class, 'index'])
        ->name('manage.activity');

});


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Register
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login']);


// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');
