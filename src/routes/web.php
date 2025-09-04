<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Global param patterns (defensive): ensure resource params are numeric.
 * This prevents "/categories/statistics" from being interpreted as {category}.
 */
Route::pattern('task', '[0-9]+');
Route::pattern('category', '[0-9]+');

/**
 * Optional: home redirect to tasks
 */
Route::get('/', fn () => redirect()->route('tasks.index'));

/* ----------------------------
 | TASKS (Inertia pages)
 * ---------------------------- */

// Resource (CRUD)
Route::resource('tasks', TaskController::class)
    ->whereNumber('task'); // enforce numeric id

// Soft delete: restore (POST to keep CSRF protection)
Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])
    ->whereNumber('id')
    ->name('tasks.restore');


/* ----------------------------
 | CATEGORIES (Inertia pages)
 * ---------------------------- */

/**
 * Put statistics BEFORE the resource to avoid being captured by {category}.
 */
Route::get('categories/statistics', [CategoryController::class, 'statistics'])
    ->name('categories.statistics');

// Resource (CRUD)
Route::resource('categories', CategoryController::class)
    ->whereNumber('category'); // enforce numeric id

// Soft delete: restore (POST)
Route::post('categories/{id}/restore', [CategoryController::class, 'restore'])
    ->whereNumber('id')
    ->name('categories.restore');